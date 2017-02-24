<?php

/**
* Implements the showcase.
*/
class StageController extends CController
{
    /**
    * Disable layout.
    */
    public $layout = false;
    
    /**
    * @var array Allowed IPs map for status action.
    */
    public $allowedIpMap = array
    (
        '77.109.141.*' => 'PMUSD', // 77.109.141.170
        '91.205.41.*' => 'PMUSD', // 91.205.41.208
        '78.41.203.*' => 'PMUSD', // 78.41.203.75
        '94.75.219.*' => 'EGUSD', // 94.75.219.187
        '178.218.161.*' => 'STUSD', // 178.218.161.122
        '178.218.169.*' => 'STUSD', // 178.218.169.72
        '82.145.55.*' => 'BCUSD', // 82.145.55.150
    );
    
    /**
    * Showcase action.
    */
    public function actionIndex()
    {
        if (DEBUG || is_null($data = cf::cache()->get('data')))
        {
            // Get groups, options and discounts from DB
            // NOTE: we don't send account discounts to client
            $data['groups'] = cf::db()->queryAll('SELECT * FROM groups WHERE enabled = TRUE');
            $data['options'] = cf::db()->queryAll('SELECT * FROM options WHERE enabled = TRUE');
            
            // Get products with keys (we need them to merge with file path info)
            $products = cf::db()->queryAllWithKeys
            (
                'SELECT P.*,'
                . ' GROUP_CONCAT(DISTINCT PG.group_id SEPARATOR \',\') groups,'
                . ' GROUP_CONCAT(DISTINCT PO.option_id SEPARATOR \',\') options'
                . ' FROM products P'
                . ' LEFT JOIN orders O ON O.product_id = P.id'
                . ' LEFT JOIN product_groups PG ON PG.product_id = P.id'
                . ' LEFT JOIN product_options PO ON PO.product_id = P.id'
                . ' WHERE P.enabled = TRUE AND (O.status IS NULL OR O.status = "WAIT")'
                . ' GROUP BY P.id'
            );
            
            // Find all discounts
            $discounts = cf::db()->queryAll('SELECT * FROM discounts WHERE enabled = TRUE');
            
            // Find master discount
            $masterDiscount = null;
            $accountDiscount = null;
            $productDiscount = array();
            foreach ($discounts as $discount)
            {
                if ($discount['account_id'] === null && $discount['product_id'] === null)
                    $masterDiscount = $discount['value'];
                
                /* if ($discount['product_id'] === null && $discount['account_id'] === @$_COOKIE['id'])
                    $accountDiscount = $discount['value']; */
                
                if ($discount['account_id'] === null && $discount['product_id'] !== null)
                    $productDiscount[$discount['product_id']] = $discount['value'];
            }
            
            // Apply discounts: '15.00' -or- '5%'
            foreach ($products as $id => $product)
            {
                $discountedPrice = $product['base_price'];
                
                if ($masterDiscount) $discountedPrice = (substr($masterDiscount, -1) == '%') ? $discountedPrice * (100 - floatval($masterDiscount)) / 100 : $discountedPrice - floatval($masterDiscount);
                if ($accountDiscount) $discountedPrice = (substr($accountDiscount, -1) == '%') ? $discountedPrice * (100 - floatval($accountDiscount)) / 100 : $discountedPrice - floatval($accountDiscount);
                if (isset($productDiscount[$id])) $discountedPrice = (substr($productDiscount[$id], -1) == '%') ? $discountedPrice * (100 - floatval($productDiscount[$id])) / 100 : $discountedPrice - floatval($productDiscount[$id]);
                
                if ($discountedPrice < 1.00) $discountedPrice = 1.00;
                
                $products[$id]['discounted_price'] = ($discountedPrice === $product['base_price']) ? false : $discountedPrice;
            }
            
            // Merge with image file paths (this is why we needed keys)
            foreach (cf::templates()->getTemplateFiles() as $id => $info)
            {
                if (isset($products[$id]))
                    foreach ($info as $name => $value)
                        $products[$id][$name] = $value;
            }
                        
            // Remove array keys from products
            $data['products'] = array_values($products);
            
            // Cache things
            if (!DEBUG)
                cf::cache()->set('data', $data);
        }
        
        // Add some lightweight parameters
        $data['debug'] = DEBUG;
        $data['urlOrder'] = cf::app()->createUrl('//stage/order');
        $data['urlStatus'] = cf::app()->createUrl('//stage/status');
        
        // Everything goes to JS application
        $this->render('index', json_encode($data));
    }
    
    /**
    * Creates an order.
    */
    public function actionOrder()
    {
        if (!$this->getIsAjaxRequest())
            throw new CException('[400] StageController::actionOrder() only serves AJAX requests.');
        
        // Order parameters
        $productId = @$_POST['product_id'];
        $projectName = @$_POST['project_name'];
        $projectTask = @$_POST['project_task'];
        $orderType = @$_POST['type'];
        $orderName = @$_POST['name'];
        $orderEmail = @$_POST['email'];
        $orderMessenger = @$_POST['messenger'];
        $orderPages = @$_POST['pages'];
        $orderOptions = @$_POST['options'];
        $orderCurrency = @$_POST['currency'];
        
        // Validate input
        if (isset($_REQUEST['calculate']))
        {
            if (!preg_match('/^\d+$/', $productId) ||
                !preg_match('/^(download|redesign|custom)$/', $orderType) ||
                !preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/i', $orderEmail) ||
                !preg_match('/^\d+$/', $orderPages) ||
                !preg_match('/^(\d{1,2}(,\d{1,2})*)?$/', $orderOptions))
                throw new CException('[400] StageController::actionOrder() request malformed.');
        }
        else
        {
            if (!preg_match('/^\d+$/', $productId) ||
                !preg_match('/^(download|redesign|custom)$/', $orderType) ||
                (!preg_match('/^[A-Z0-9 ]+$/i', $projectName) && $orderType !== 'download') ||
                (!preg_match('/^[A-Z0-9 ]+$/i', $projectTask) && $orderType !== 'download') ||
                !preg_match('/^[A-Z ]+$/i', $orderName) ||
                !preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/i', $orderEmail) ||
                !preg_match('/^[A-Z0-9 ]+$/i', $orderMessenger) ||
                !preg_match('/^\d+$/', $orderPages) ||
                !preg_match('/^(\d{1,2}(,\d{1,2})*)?$/', $orderOptions) ||
                !preg_match('/^(pm|eg|st|bc)usd$/', $orderCurrency))
                throw new CException('[400] StageController::actionOrder() request malformed.');
        }
        
        // Get account and product
        $account = cf::db()->queryRow('SELECT * FROM accounts WHERE email = ? AND enabled = TRUE', $orderEmail);
        $product = cf::db()->queryRow('SELECT * FROM products WHERE id = ? AND enabled = TRUE', $productId);
        
        // Get selected options
        $values = explode(',', $orderOptions); $placeholders = implode(',', array_fill(0, count($values), '?'));
        $sql = 'SELECT * FROM options WHERE id IN (' . $placeholders . ') AND enabled = TRUE';
        $options = cf::db()->queryAll($sql, $values);
        
        // Calculate order price
        $price = floatval($product['base_price']);
        $price += intval($orderPages) * floatval($product['page_price']);
        $price += ($orderType === 'redesign') ? floatval($product['extra_price']) : 0.00;
        foreach ($options as $option) $price += floatval($option['price']);
        
        // Populate discounts
        // NOTE: Group discounts are not implemented here since one product
        //       may have more than one group and it's not clear what group discount to apply.
        $masterDiscount = null; $accountDiscount = null; $productDiscount = null; $discounts = array();
        foreach (cf::db()->queryAll('SELECT * FROM discounts WHERE enabled = TRUE') as $discount)
        {
            if ($discount['account_id'] === null && $discount['product_id'] === null) {$masterDiscount = $discount['value']; $discounts[$discount['id']] = $discount;}
            if ($discount['product_id'] === null && $discount['account_id'] === $account['id']) {$accountDiscount = $discount['value']; $discounts[$discount['id']] = $discount;}
            if ($discount['account_id'] === null && $discount['product_id'] === $product['id']) {$productDiscount = $discount['value']; $discounts[$discount['id']] = $discount;}
        }
        
        // Apply discounts: '15.00' -or- '5%'
        $discountedPrice = $price;
        if ($masterDiscount) $discountedPrice = (substr($masterDiscount, -1) == '%') ? $discountedPrice * (100 - floatval($masterDiscount)) / 100 : $discountedPrice - floatval($masterDiscount);
        if ($accountDiscount) $discountedPrice = (substr($accountDiscount, -1) == '%') ? $discountedPrice * (100 - floatval($accountDiscount)) / 100 : $discountedPrice - floatval($accountDiscount);
        if ($productDiscount) $discountedPrice = (substr($productDiscount, -1) == '%') ? $discountedPrice * (100 - floatval($productDiscount)) / 100 : $discountedPrice - floatval($productDiscount);
        
        // Price limitation
        if ($discountedPrice < 1.00) $discountedPrice = 1.00;
        
        // Price calculation request
        if (isset($_POST['calculate']))
            $this->renderJSON(array('price' => $price, 'discounted_price' => $discountedPrice));
        
        // Find customer account or auto-register account
        if (!is_array($account = cf::db()->queryRow('SELECT * FROM accounts WHERE email = ?', $orderEmail)))
        {
            cf::db()->execute
            (
                'INSERT INTO accounts SET name = ?, email = ?, messenger = ?, created = ?',
                    $orderName, $orderEmail, $orderMessenger, time()
            );
            
            $account = array
            (
                'id' => cf::db()->getLastInsertId(),
                'name' => $orderName,
                'email' => $orderEmail,
                'messenger' => $orderMessenger,
                'created' => time(),
            );
        }
        
        // Create order
        
        cf::db()->execute
        (
            'INSERT INTO orders SET account_id = ?, product_id = ?, project_name = ?, project_task = ?, type = ?, status = ?, price = ?, created = ?',
                $account['id'], $productId, $projectName, $projectTask, $orderType, 'WAIT', $discountedPrice, time()
        );
        
        $order = array
        (
            'id' => cf::db()->getLastInsertId(),
            'account_id' => $account['id'],
            'product_id' => $productId,
            'project_name' => $projectName,
            'project_task' => $projectTask,
            'type' => $orderType,
            'status' => 'WAIT',
            'price' => $discountedPrice,
            'created' => time(),
        );
        
        // Create order options
        if (count($options) > 0)
        {
            $values = array(); foreach ($options as $option) $values[] = '(' . $order['id'] . ', ' . $option['id'] . ')';
            cf::db()->execute('INSERT INTO order_options (order_id, option_id) VALUES ' . implode(',', $values));
        }
        
        // Create order discounts
        if (count($discounts) > 0)
        {
            $values = array(); foreach ($discounts as $discount) $values[] = '(' . $order['id'] . ', ' . $discount['id'] . ')';
            cf::db()->execute('INSERT INTO order_discounts (order_id, discount_id) VALUES ' . implode(',', $values));
        }
        
        // Get payment gateway
        if (is_null($gateway = cf::app()->getComponent($orderCurrency)))
            throw new CException('[CONFIG] Could not retrieve payment gateway: %s', $orderCurrency);
        
        // For custom orders only ask for 50% of the price
        if ($orderType === 'custom') $discountedPrice /= 2;
        
        // Create SCI form
        $paymentForm = $gateway->renderForm
        (
            $order['id'], $discountedPrice,
            cf::app()->createUrl('http://stage/status'),
            cf::app()->createUrl('http://', array('#' => 'success')),
            cf::app()->createUrl('http://', array('#' => 'failure'))
        );
        
        // Return SCI form for payment
        $this->renderJSON(array(
            'payment_form' => $paymentForm,
            'order_id' => cf::security()->encrypt($order['id']),
            'order_price' => $discountedPrice,
        ));
    }
    
    /**
    * Accept status requests.
    * TODO: Add template parameters to ticket and email (such as number of pages and options)
    * TODO: Send helpdesk login information in payment confirmation email
    */
    public function actionStatus()
    {
        if (isset($_GET['order_id']) && is_numeric($orderId = cf::security()->decrypt($_GET['order_id'])))
            $this->renderJSON(array('order_status' => cf::db()->queryScalar('SELECT status FROM orders WHERE id = ?', $orderId)));
        
        if (!isset($this->allowedIpMap[$addr = preg_replace('/\d+$/', '*', $ip = $_SERVER['REMOTE_ADDR'])]))
            throw new CException('[HACK] Status request comes from an untrusted IP: %s', $ip);
        
        if (is_null($system = cf::app()->getComponent($currency = $this->allowedIpMap[$addr])))
            throw new CException('[HACK] Status request specifies an invalid system_id: %s', $currency);
        
        if ($system->acceptStatus($orderId, $amount, $account, $batch, $error) !== true)
            throw new CException('[HACK] Status request error: %s', $error);
        
        if (!is_array($order = cf::db()->queryRow('SELECT O.*, A.name, A.email, A.messenger FROM orders O LEFT JOIN accounts A ON A.id = O.account_id WHERE O.id = ?', $orderId)))
            throw new CException('[HACK] Status request data has been forged: %s', $orderId);
        
        // Add parameters
        $order['batch'] = $batch;
        $order['account'] = $account;
        $order['amount'] = $amount;
        $order['currency'] = $currency;
        
        // mail('sergeymorkovkin@gmail.com', 'DEBUG', print_r($order, true));
        
        if ($amount < $order['price'] / ($order['type'] === 'custom' ? 2 : 1))
            throw new CException('[HACK] Payment amount is lower than order price');
        
        // Update order status
        cf::db()->execute('UPDATE orders SET status = ? WHERE id = ?', 'PAID', $orderId);
        
        // Create history record
        cf::db()->execute('INSERT INTO history SET account_id = ?, product_id = ?, order_id = ?, type = ?, param_1 = ?, param_2 = ?, param_3 = ?, created = ?', $order['account_id'], $order['product_id'], $order['id'], 'PAYMENT_RECEIVED', $amount, $account, $batch, time());
        
        // Different order types
        if (($type = strtolower($order['type'])) !== 'download')
        {
            // Create helpdesk ticket
            cf::helpdesk()->createTicket("{$order['name']} <{$order['email']}>", "NEW: {$order['project_name']}", $order['project_task']);
            
            // Send email message
            cf::email()->sendMessage($type . '.order.inwork', $order['email'], $order);
        }
        else
        {
            // Download order parameters
            $order['link'] = cf::app()->createUrl('//stage/download', array('hash' => cf::security()->encrypt($order['id'])));
            
            // Send email message
            cf::email()->sendMessage('download.order.closed', $order['email'], $order);
        }
        
        // Clear cached catalog
        cf::cache()->set('data', null);
    }
    
    /**
    * Template download.
    */
    public function actionDownload()
    {
        // Get order ID from request
        $orderId = cf::security()->decrypt($_GET['hash']);
        
        // Get order from DB
        if (!is_array($order = cf::db()->queryRow('SELECT * FROM orders WHERE id = ?', $orderId)))
            throw new CException('Requested template was not found: %s', $orderId);
        
        // Check order status
        if ($order['status'] === 'WAIT')
            throw new CException('Requested template order was not paid: %s', $orderId);
        
        // Update order status
        cf::db()->execute('UPDATE orders SET status = ? WHERE id = ?', 'DONE', $orderId);
        
        // Download template
        cf::templates()->downloadTemplate($order['product_id']);
        
        // Create history record
        cf::db()->execute('INSERT INTO history SET account_id = ?, product_id = ?, order_id = ?, type = ?, param_1 = ?, param_2 = ?, param_3 = ?, created = ?', $order['account_id'], $order['product_id'], $order['id'], 'DOWNLOAD_FILES', null, null, null, time());

    }
}