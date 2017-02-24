<?php

/**
* Admin panel.
* TODO: Statistics should load with page and should not blink.
* TODO: Add unified format for data delivery on the client (one chunk or all together).
*/
class AdminController extends CController
{
    const REGEX_EMAIL = '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/i';
    
    /**
    * @var string Controller layout.
    */
    public $layout = false;
    
    /**
    * Renders JS application.
    */
    public function actionIndex()
    {
        $this->render('index', json_encode(array
        (
            'debug' => DEBUG,
            'login' => cf::login()->checkLogin(),
            'urlLogin' => cf::app()->createUrl('//admin/login'),
            'urlLogout' => cf::app()->createUrl('//admin/logout'),
            'urlStats' => cf::app()->createUrl('//admin/stats'),
            'urlOrderList' => cf::app()->createUrl('//admin/orderList'),
            'urlProductList' => cf::app()->createUrl('//admin/productList'),
            'urlOptionList' => cf::app()->createUrl('//admin/optionList'),
            'urlDiscountList' => cf::app()->createUrl('//admin/discountList'),
            'urlGroupList' => cf::app()->createUrl('//admin/groupList'),
            'urlHistory' => cf::app()->createUrl('//admin/history'),
            'urlExport' => cf::app()->createUrl('//admin/export'),
            'urlFileUpload' => cf::app()->createUrl('//admin/fileUpload'),
        )));
    }
    
    /**
    * Admin login.
    */
    public function actionLogin()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionLogin() only serves AJAX requests.');
        
        $this->renderJSON(array('result' => cf::login()->login
        (
            @$_POST['username'],
            @$_POST['password'],
            @$_POST['remember']
        )));
    }
    
    /**
    * Admin logout.
    */
    public function actionLogout()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionLogout() only serves AJAX requests.');
        
        $this->renderJSON(array('result' => cf::login()->logout()));
    }
    
    /**
    * Statistics.
    */
    public function actionStats()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionStats() only serves AJAX requests.');
        
        if (!cf::login()->checkLogin())
            throw new CException('[401] Not authorized.');
        
        // Remove unused uploads
        $tempDir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        foreach (glob($tempDir . '/*.shop-upload') as $file)
            if (is_file($file) && time() - filemtime($file) > 3600) unlink($file);
        
        $data = cf::db()->queryRow
        (
            'SELECT '
            . ' (SELECT COUNT(*) FROM orders) orders_total,'
            . ' (SELECT COUNT(*) FROM orders WHERE status="WAIT") orders_waiting,'
            . ' (SELECT COUNT(*) FROM orders WHERE status="WORK") orders_in_work,'
            . ' (SELECT COUNT(*) FROM orders WHERE status="DONE") orders_done,'
            . ' (SELECT COUNT(*) FROM accounts) accounts_total,'
            . ' (SELECT COUNT(*) FROM products) products_total;'
        );
        
        $this->renderJSON($data);
    }
    
    /**
    * Order list.
    */
    public function actionOrderList()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionOrderList() only serves AJAX requests.');
        
        if (!cf::login()->checkLogin())
            throw new CException('[401] Not authorized.');
        
        // Insert / update order
        if (isset($_GET['insert']))
        {
            if (!preg_match('/^$|^\d+$/', @$_GET['id']) || 
                !preg_match('/^(|work|done)$/i', @$_GET['state']) || 
                !preg_match('/^[\w\W\d\D\s]+$/iu', @$_GET['project_name']) || 
                !preg_match('/^[\w\W\d\D\s]+$/iu', @$_GET['project_task']))
                throw new CException('Invalid input.');
            
            // Insert or update
            cf::db()->execute
            (
                'INSERT INTO orders SET project_name = ?, project_task = ?, id = ?'
                . ' ON DUPLICATE KEY UPDATE project_name = ?, project_task = ?, status = IF(? IS NULL, status, ?)', 
                $_GET['project_name'], $_GET['project_task'], @$_GET['id'], 
                $_GET['project_name'], $_GET['project_task'], @$_GET['state'], @$_GET['state']
            );
            
            // Get template ID
            $orderId = empty($_GET['id']) ? cf::db()->getLastInsertId() : intval($_GET['id']);
            $order = cf::db()->queryRow
            (
                'SELECT A.name, A.email, A.messenger, P.name product_name, O.*'
                . ' FROM orders O'
                . ' LEFT JOIN accounts A ON A.id = O.account_id'
                . ' LEFT JOIN products P ON P.id = O.product_id'
                . ' WHERE O.id = ?',
                    $orderId
            );
            
            // File management
            if (!isset($_GET['upload_zip']) || empty($_GET['upload_zip'])) {}
            
            // Delete archive
            else if ($_GET['upload_zip'] === 'delete')
                cf::templates()->removeTemplateFiles($order['product_id'], true, false);
            
            // Upload archive
            else if (!is_null($zipPath = cf::security()->decrypt($_GET['upload_zip'])))
                cf::templates()->uploadTemplateFiles($order['product_id'], $zipPath, false);
            
            
            // Send customer notification emails
            if (isset($_GET['state']) && $order['status'] === 'DONE' && $order['type'] !== 'DOWNLOAD')
            {
                // Download order parameters
                $order['link'] = cf::app()->createUrl('//stage/download', array('hash' => cf::security()->encrypt($order['id'])));
                
                // Send emails
                if ($order['type'] === 'REDESIGN') cf::email()->send('redesign.order.closed.eml', $order);
                if ($order['type'] === 'CUSTOM') cf::email()->send('custom.order.closed.eml', $order);
            }
        }
        
        // Delete order and it's dependencies
        if (isset($_GET['delete']))
        {
            cf::db()->execute('DELETE FROM orders WHERE id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM order_options WHERE order_id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM order_discounts WHERE order_id = ?', intval($_GET['delete']));
        }
        
        $sql = 'SELECT SQL_CALC_FOUND_ROWS A.name, A.email, A.messenger, P.name product_name, O.*,'
            . ' GROUP_CONCAT(DISTINCT O2.name SEPARATOR \', \') options'
            . ' FROM orders O'
            . ' LEFT JOIN accounts A ON A.id = O.account_id'
            . ' LEFT JOIN products P ON P.id = O.product_id'
            . ' LEFT JOIN order_options O1 ON O1.order_id = O.id'
            . ' LEFT JOIN options O2 ON O2.id = O1.option_id'
            . ' GROUP BY O.id';
        
        // Order filtering
        if (isset($_GET['sFilter']))
        {
            switch ($_GET['sFilter'])
            {
                case 'orders_waiting': $sql .= ' AND status = "WAIT"'; break;
                case 'orders_in_work': $sql .= ' AND status = "WORK"'; break;
                case 'orders_done': $sql .= ' AND status = "DONE"'; break;
                default: break;
            }
        }
        
        // Order sorting
        if (isset($_GET['iSortingCols']))
        {
            $order = array();
            $dirs = array('asc', 'desc');
            $sort = array('id', 'project_name', 'price', 'status', 'type', 'created');
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++)
            {
                if (isset($_GET["mDataProp_$i"]) && isset($_GET["sSortDir_$i"]))
                {
                    if (in_array($col = $_GET["mDataProp_" . $_GET["iSortCol_$i"]], $sort) &&
                        in_array($dir = $_GET["sSortDir_$i"], $dirs))
                        $order[] = "$col $dir";
                }
            }
            
            $sql .= ' ORDER BY ' . ($order ? implode(', ', $order) : 'created DESC');
        }
        
        // Order pagination
        if (isset($_GET['iDisplayStart']))
        {
            $offset = intval($_GET['iDisplayStart']);
            $length = intval($_GET['iDisplayLength']);
            $sql .= ' LIMIT ' . $offset . ',' . $length;
        }
        
        // cf::db()->execute('SET CHARACTER SET utf8');
        
        $data = cf::db()->queryAll($sql);
        $size = cf::db()->queryScalar('SELECT FOUND_ROWS()');
        
        // Post-process messages
        $files = cf::templates()->getTemplateFiles();
        // header('Content-Type: text/plain'); print_r($files); exit();
        foreach ($data as $key => $row)
        {
            $data[$key]['type:label'] = cf::messages()->text('ORDER_TYPES/' . $row['type']);
            $data[$key]['status:label'] = cf::messages()->text('ORDER_STATUSES/' . $row['status']);
            
            $data[$key]['state_wait'] = ($row['status'] === 'WAIT');
            $data[$key]['state_paid'] = ($row['status'] === 'PAID');
            $data[$key]['state_work'] = ($row['status'] === 'WORK');
            $data[$key]['state_done'] = ($row['status'] === 'DONE');
            
            $data[$key]['tplPath'] = isset($files[$row['product_id']]['tplPath']) ? $files[$row['product_id']]['tplPath'] : null;
            $data[$key]['imgPath'] = isset($files[$row['product_id']]['thumbPath']) ? $files[$row['product_id']]['thumbPath'] : null;
        }
        
        $this->renderJSON(array(
            'aaData' => $data,
            'iTotalRecords' => $size,
            'iTotalDisplayRecords' => $size,
            'sEcho' => intval(@$_GET['sEcho']),
        ));
    }
    
    /**
    * Product list.
    */
    public function actionProductList()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionProductList() only serves AJAX requests.');
        
        if (!cf::login()->checkLogin())
            throw new CException('[401] Not authorized.');
        
        // Lists data
        if (isset($_GET['lists']))
        {
            return $this->renderJSON(array(
                'groups' => cf::db()->queryAll('SELECT * FROM groups'),
                'options' => cf::db()->queryAll('SELECT * FROM options'),
            ));
        }
        
        // Insert / update order
        if (isset($_GET['insert']))
        {
            if (!preg_match('/^$|^\d+$/', @$_GET['id']) || 
                !preg_match('/^[\w\W\d\D\s]+$/iu', @$_GET['name']) || 
                !preg_match('/^\d+(\.\d{1,2})?$/', @$_GET['base_price']) ||
                !preg_match('/^\d+(\.\d{1,2})?$/', @$_GET['page_price']) ||
                !preg_match('/^\d+(\.\d{1,2})?$/', @$_GET['extra_price']) ||
                !preg_match('/^(\d+(\,\s?\d+)*)?$/', @$_GET['groups']) ||
                !preg_match('/^(\d+(\,\s?\d+)*)?$/', @$_GET['options']))
                throw new CException('Invalid input.');
            
            // Insert or update
            cf::db()->execute
            (
                'INSERT INTO products SET name = ?, base_price = ?, page_price = ?, extra_price = ?, id = ?'
                . ' ON DUPLICATE KEY UPDATE name = ?, base_price = ?, page_price = ?, extra_price = ?', 
                $_GET['name'], $_GET['base_price'], $_GET['page_price'], $_GET['extra_price'], @$_GET['id'], 
                $_GET['name'], $_GET['base_price'], $_GET['page_price'], $_GET['extra_price']
            );
            
            // Get product ID
            $productId = empty($_GET['id']) ? cf::db()->getLastInsertId() : intval($_GET['id']);
            
            // Remove all options and groups
            cf::db()->execute('DELETE FROM product_groups WHERE product_id = ?', $productId);
            cf::db()->execute('DELETE FROM product_options WHERE product_id = ?', $productId);
            
            // Create selected groups
            if (!empty($_GET['groups']) && count($groups = preg_split('/\,\s*/', $_GET['groups'])) > 0)
            {
                $values = array(); foreach ($groups as $groupId) $values[] = '(' . $productId . ', ' . $groupId . ')';
                cf::db()->execute('INSERT INTO product_groups (product_id, group_id) VALUES ' . implode(',', $values));
            }
            
            // Create selected options
            if (!empty($_GET['options']) && count($options = preg_split('/\,\s*/', $_GET['options'])) > 0)
            {
                $values = array(); foreach ($options as $optionId) $values[] = '(' . $productId . ', ' . $optionId . ')';
                cf::db()->execute('INSERT INTO product_options (product_id, option_id) VALUES ' . implode(',', $values));
            }
            
            // File management
            if (!isset($_GET['upload_zip']) || empty($_GET['upload_zip'])) {}
            else if ($_GET['upload_zip'] === 'delete') cf::templates()->removeTemplateFiles($productId, true, false);
            else if (!is_null($zipPath = cf::security()->decrypt($_GET['upload_zip']))) cf::templates()->uploadTemplateFiles($productId, $zipPath, false);
            
            // File management
            if (!isset($_GET['upload_img']) || empty($_GET['upload_img'])) {}
            else if ($_GET['upload_img'] === 'delete') cf::templates()->removeTemplateFiles($productId, false, true);
            else if (!is_null($imgPath = cf::security()->decrypt($_GET['upload_img']))) cf::templates()->uploadTemplateFiles($productId, false, $imgPath);
        }
        
        // Delete product and it's dependencies
        if (isset($_GET['delete']) && intval($_GET['delete']) != 1)
        {
            cf::db()->execute('DELETE FROM products WHERE id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM product_groups WHERE product_id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM product_options WHERE product_id = ?', intval($_GET['delete']));
        }
        
        $sql = 'SELECT SQL_CALC_FOUND_ROWS P.*, IF(O.status IS NULL OR O.status = "WAIT" OR P.id = 1, "SALE", "SOLD") status,'
            . ' GROUP_CONCAT(DISTINCT PG.group_id SEPARATOR \', \') groups,'
            . ' GROUP_CONCAT(DISTINCT PO.option_id SEPARATOR \', \') options'
            . ' FROM products P'
            . ' LEFT JOIN orders O ON O.product_id = P.id'
            . ' LEFT JOIN product_groups PG ON PG.product_id = P.id'
            . ' LEFT JOIN product_options PO ON PO.product_id = P.id'
            . ' GROUP BY P.id';
        
        // Order sorting
        if (isset($_GET['iSortingCols']))
        {
            $order = array();
            $dirs = array('asc', 'desc');
            $sort = array('id', 'name', 'base_price', 'page_price', 'extra_price', 'created');
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++)
            {
                if (isset($_GET["mDataProp_$i"]) && isset($_GET["sSortDir_$i"]))
                {
                    if (in_array($col = $_GET["mDataProp_" . $_GET["iSortCol_$i"]], $sort) &&
                        in_array($dir = $_GET["sSortDir_$i"], $dirs))
                        $order[] = "$col $dir";
                }
            }
            
            $sql .= ' ORDER BY ' . ($order ? implode(', ', $order) : 'created DESC');
        }
        
        // Order pagination
        if (isset($_GET['iDisplayStart']))
        {
            $offset = intval($_GET['iDisplayStart']);
            $length = intval($_GET['iDisplayLength']);
            $sql .= ' LIMIT ' . $offset . ',' . $length;
        }
        
        // Delete product and it's dependencies
        if (isset($_GET['delete']))
        {
            cf::db()->execute('DELETE FROM products WHERE id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM product_groups WHERE product_id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM product_options WHERE product_id = ?', intval($_GET['delete']));
        }
        
        $data = cf::db()->queryAll($sql);
        $size = cf::db()->queryScalar('SELECT FOUND_ROWS()');
        
        // Post-process messages
        $files = cf::templates()->getTemplateFiles();
        foreach ($data as $key => $row)
        {
            $data[$key]['status'] = cf::messages()->text('PRODUCT_STATUSES/' . $row['status']);
            $data[$key]['deletable'] = ($row['id'] != 1);
            $data[$key]['tplPath'] = isset($files[$row['id']]['tplPath']) ? $files[$row['id']]['tplPath'] : null;
            $data[$key]['imgPath'] = isset($files[$row['id']]['thumbPath']) ? $files[$row['id']]['thumbPath'] : null;
        }
        
        $this->renderJSON(array(
            'aaData' => $data,
            'iTotalRecords' => $size,
            'iTotalDisplayRecords' => $size,
            'sEcho' => intval(@$_GET['sEcho']),
        ));
    }
    
    /**
    * Option list.
    */
    public function actionOptionList()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionOptionList() only serves AJAX requests.');
        
        if (!cf::login()->checkLogin())
            throw new CException('[401] Not authorized.');
            
        // Delete option and it's dependencies
        if (isset($_GET['delete']))
        {
            cf::db()->execute('DELETE FROM options WHERE id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM order_options WHERE option_id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM product_options WHERE option_id = ?', intval($_GET['delete']));
        }
        
        // Insert and update
        if (isset($_GET['insert']))
        {
            if (!preg_match('/^$|^\d+$/', @$_GET['id']) || 
                !preg_match('/^[\w\d ]+$/u', @$_GET['name']) || 
                !preg_match('/^\d+(\.\d{1,2})?$/', @$_GET['price']) ||
                !preg_match('/^$|^on$/', @$_GET['enabled']))
                throw new CException('Invalid input.');
            
            // Insert or update
            cf::db()->execute
            (
                'INSERT INTO options SET enabled = ?, name = ?, price = ?, id = ?'
                . ' ON DUPLICATE KEY UPDATE enabled = ?, name = ?, price = ?', 
                isset($_GET['enabled']), $_GET['name'], $_GET['price'], @$_GET['id'], 
                isset($_GET['enabled']), $_GET['name'], $_GET['price']
            );
        }
        
        $data = cf::db()->queryAll('SELECT SQL_CALC_FOUND_ROWS O.* FROM options O;');
        $size = cf::db()->queryScalar('SELECT FOUND_ROWS()');
        
        // Post-process messages
        foreach ($data as $key => $row)
        {
            $data[$key]['enabled:label'] = cf::messages()->text('ENABLED/' . ($row['enabled'] ? 'YES' : 'NO'));
            $data[$key]['enabled'] = $row['enabled'] ? true : false;
        }
        
        $this->renderJSON(array(
            'aaData' => $data,
            'iTotalRecords' => $size,
            'iTotalDisplayRecords' => $size,
            'sEcho' => intval(@$_GET['sEcho']),
        ));
    }
    
    /**
    * Discount list.
    */
    public function actionDiscountList()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionDiscountList() only serves AJAX requests.');
        
        if (!cf::login()->checkLogin())
            throw new CException('[401] Not authorized.');
        
        // Autocomplete product names
        if (isset($_GET['q']))
        {
            $list1 = cf::db()->queryColumn('SELECT name FROM products WHERE name LIKE ? LIMIT 5;', '%' . $_GET['q'] . '%');
            $list2 = cf::db()->queryColumn('SELECT email FROM accounts WHERE email LIKE ? LIMIT 5;', '%' . $_GET['q'] . '%');
            echo implode("\n", $list1) . "\n" . implode("\n", $list2); exit();
        }
        
        // Validate discount target
        if (isset($_POST['target']))
        {
            $this->renderJSON(array('result' => cf::db()->queryScalar('SELECT count(*) FROM products WHERE name = ?', $_POST['target']) > 0));
        }
        
        // Delete discount and it's dependencies
        if (isset($_GET['delete']) && intval($_GET['delete']) != 1)
        {
            cf::db()->execute('DELETE FROM discounts WHERE id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM order_discounts WHERE discount_id = ?', intval($_GET['delete']));
        }
        
        // Insert and update
        if (isset($_GET['insert']))
        {
            // Validate discount value
            if (!preg_match('/^$|^\d+$/', @$_GET['id']) || 
                !preg_match('/^(\d+(\.\d{1,2})?|\d+\%)$/', @$_GET['value']) ||
                !preg_match('/^$|^on$/', @$_GET['enabled']))
                throw new CException('Invalid input.');
            
            $email = preg_match(self::REGEX_EMAIL, $target = @$_GET['target']); $id = intval(@$_GET['id']);
            $accountId = cf::db()->queryScalar('SELECT id FROM accounts WHERE email = ?', $target);
            $productId = cf::db()->queryScalar('SELECT id FROM products WHERE name = ?', $target);
            
            // Auto-create account for binding
            if (!$accountId && $email) $accountId = cf::db()->insert('accounts', array('email' => $target, 'created' => time()));
            
            // No normalize variables
            if ($accountId || $id === 1) $productId = null;
            if ($productId || $id === 1) $accountId = null;
            
            // Insert or update discount
            cf::db()->execute
            (
                'INSERT INTO discounts SET enabled = ?, account_id = ?, product_id = ?, value = ?, id = ?'
                . ' ON DUPLICATE KEY UPDATE enabled = ?, account_id = ?, product_id = ?, value = ?', 
                isset($_GET['enabled']), $accountId, $productId, @$_GET['value'], $id, 
                isset($_GET['enabled']), $accountId, $productId, @$_GET['value']
            );
        }
        
        $data = cf::db()->queryAll
        (
            'SELECT SQL_CALC_FOUND_ROWS D.*, A.email account, P.name product FROM discounts D'
            . ' LEFT JOIN accounts A ON A.id = D.account_id'
            . ' LEFT JOIN products P ON P.id = D.product_id'
            . ' GROUP BY D.id'
        );
        
        $size = cf::db()->queryScalar('SELECT FOUND_ROWS()');
        
        // Post-process messages
        foreach ($data as $key => $row)
        {
            if (is_null($row['account_id']) && is_null($row['product_id']))
                $data[$key]['destination:label'] = cf::messages()->text('DISCOUNT_DESTINATIONS/GENERAL');
            
            if (is_null($row['account_id']) && !is_null($row['product_id']))
            {
                $data[$key]['destination:label'] = cf::messages()->text('DISCOUNT_DESTINATIONS/PRODUCT', $row['product']);
                $data[$key]['target'] = $row['product'];
            }
            
            if (is_null($row['product_id']) && !is_null($row['account_id']))
            {
                $data[$key]['destination:label'] = cf::messages()->text('DISCOUNT_DESTINATIONS/ACCOUNT', $row['account']);
                $data[$key]['target'] = $row['account'];
            }

            $data[$key]['enabled:label'] = cf::messages()->text('ENABLED/' . ($row['enabled'] ? 'YES' : 'NO'));
            
            $data[$key]['enabled'] = $row['enabled'] ? true : false;
            
            $data[$key]['deletable'] = ($row['id'] != 1);
        }
        
        $this->renderJSON(array(
            'aaData' => $data,
            'iTotalRecords' => $size,
            'iTotalDisplayRecords' => $size,
            'sEcho' => intval(@$_GET['sEcho']),
        ));
    }
    
    /**
    * Group list.
    */
    public function actionGroupList()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionGroupList() only serves AJAX requests.');
        
        if (!cf::login()->checkLogin())
            throw new CException('[401] Not authorized.');
        
        // Delete group and it's dependencies
        if (isset($_GET['delete']))
        {
            cf::db()->execute('DELETE FROM groups WHERE id = ?', intval($_GET['delete']));
            cf::db()->execute('DELETE FROM product_groups WHERE group_id = ?', intval($_GET['delete']));
        }
        
        // Insert and update
        if (isset($_GET['insert']))
        {
            if (!preg_match('/^$|^\d+$/', @$_GET['id']) || 
                !preg_match('/^[\w\d ]+$/u', @$_GET['name']) || 
                !preg_match('/^(STANDARD|PREMIUM|NEW)$/', @$_GET['type']) ||
                !preg_match('/^$|^on$/', @$_GET['enabled']))
                throw new CException('Invalid input.');
            
            // Insert or update
            cf::db()->execute
            (
                'INSERT INTO groups SET enabled = ?, name = ?, type = ?, id = ?'
                . ' ON DUPLICATE KEY UPDATE enabled = ?, name = ?, type = ?', 
                isset($_GET['enabled']), $_GET['name'], $_GET['type'], @$_GET['id'], 
                isset($_GET['enabled']), $_GET['name'], $_GET['type']
            );
        }
        
        $data = cf::db()->queryAll('SELECT SQL_CALC_FOUND_ROWS G.* FROM groups G;');
        $size = cf::db()->queryScalar('SELECT FOUND_ROWS()');
        
        // Post-process messages
        foreach ($data as $key => $row)
        {
            $data[$key]['enabled:label'] = cf::messages()->text('ENABLED/' . ($row['enabled'] ? 'YES' : 'NO'));
            $data[$key]['enabled'] = $row['enabled'] ? true : false;
        }
        
        $this->renderJSON(array(
            'aaData' => $data,
            'iTotalRecords' => $size,
            'iTotalDisplayRecords' => $size,
            'sEcho' => intval(@$_GET['sEcho']),
        ));
    }
    
    /**
    * View history.
    */
    public function actionHistory()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionHistory() only serves AJAX requests.');
        
        if (!cf::login()->checkLogin())
            throw new CException('[401] Not authorized.');
        
        $sql = 'SELECT SQL_CALC_FOUND_ROWS H.*, A.name'
            . ' FROM history H'
            . ' LEFT JOIN accounts A ON A.id = H.account_id'
            . ' LEFT JOIN products P ON P.id = H.product_id'
            . ' LEFT JOIN orders O ON O.id = H.order_id';
        
        // Sorting
        if (isset($_GET['iSortingCols']))
        {
            $order = array();
            $dirs = array('asc', 'desc');
            $sort = array('type', 'param_1', 'param_2', 'param_3', 'created');
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++)
            {
                if (isset($_GET["mDataProp_$i"]) && isset($_GET["sSortDir_$i"]))
                {
                    if (in_array($col = $_GET["mDataProp_" . $_GET["iSortCol_$i"]], $sort) &&
                        in_array($dir = $_GET["sSortDir_$i"], $dirs))
                        $order[] = "$col $dir";
                }
            }
            
            $sql .= ' ORDER BY ' . ($order ? implode(', ', $order) : 'created DESC');
        }
        
        // History pagination
        if (isset($_GET['iDisplayStart']))
        {
            $offset = intval($_GET['iDisplayStart']);
            $length = intval($_GET['iDisplayLength']);
            $sql .= ' LIMIT ' . $offset . ',' . $length;
        }
        
        $data = cf::db()->queryAll($sql);
        $size = cf::db()->queryScalar('SELECT FOUND_ROWS()');
        
        // Post-process messages
        foreach ($data as $key => $row)
            $data[$key]['message'] = cf::messages()->text('HISTORY/' . $row['type'], $row['param_1'], $row['param_2'], $row['param_3']);
        
        $this->renderJSON(array(
            'aaData' => $data,
            'iTotalRecords' => $size,
            'iTotalDisplayRecords' => $size,
            'sEcho' => intval(@$_GET['sEcho']),
        ));
    }
    
    /**
    * Export emails
    */
    public function actionExport()
    {
        /* if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionLogout() only serves AJAX requests.'); */
        
        if (!cf::login()->checkLogin())
            throw new CException('[401] Not authorized.');
        
        $emails = implode("\r\n", cf::db()->queryColumn('SELECT DISTINCT email FROM accounts;'));
        
        // Disable caching
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

        // Force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // Disposition / encoding on response body
        header("Content-Disposition: attachment;filename=emails.txt");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . strlen($emails) . ";\n");
        
        echo $emails;
    }
    
    /**
    * File uploads
    */
    public function actionFileUpload()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] AdminController::actionGroupList() only serves AJAX requests.');
        
        if (!cf::login()->checkLogin())
            throw new CException('[401] Not authorized.');
        
        if (!is_null($fileName = @$_FILES['upload']['tmp_name']))
        {
            move_uploaded_file($fileName, $fileName = $fileName . '.shop-upload');
            
            if (($imgSize = getimagesize($fileName)) !== false)
            {
                // Create image thumbnail and encode it
                $srcImage = imagecreatefromstring(file_get_contents($fileName));
                list($srcWidth, $srcHeight) = $imgSize;
                list($dstWidth, $dstHeight) = array(184, 166);
                $tmpWidth = $dstWidth; $tmpHeight = $dstWidth * $srcHeight / $srcWidth;
                $dstImage = imagecreatetruecolor($dstWidth, $dstHeight);
                imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $tmpWidth, $tmpHeight, $srcWidth, $srcHeight);
                ob_start(); imagejpeg($dstImage, null, 85); $preview = base64_encode(ob_get_clean());
                
                $this->renderJSON(array('upload' => cf::security()->encrypt($fileName), 'preview' => $preview));
            }
            else
                $this->renderJSON(array('upload' => cf::security()->encrypt($fileName)));
        }
        
    }
}