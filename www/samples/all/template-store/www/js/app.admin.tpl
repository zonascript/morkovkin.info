<script id="login" type="text/html">
    
    <div class="header">
        <div class="wrap">
            <div class="menu float-left">
                <ul><li><a href="index.php" title=""><i class="icon icon-4"></i><span>Main website</span></a></li></ul>
            </div>
            <div class="menu float-right">
                <ul><li><a href="#" title=""><i class="icon icon-6"></i><span>Contact admin</span></a></li></ul>
            </div>
        </div>
    </div>
    
    <div class="login">
        <div class="logo"><img src="img/logo.png" alt="" /></div>
        <div class="alert information hidden message-logged-out"><i class="icon"></i><p>You have been successfully logged out</p></div>
        <div class="alert warning hidden message-invalid-login"><i class="icon"></i><p>Specified credentials are not valid</p></div>
        
        <div class="widget">
            <div class="head"><h5><i class="icon icon-user"></i>Login</h5></div>
            <form class="form" action="{{urlLogin}}" method="POST">
                <div class="field no-border">
                    <label class="label" for="username">Username:</label>
                    <input class="input" id="username" name="username" type="text" value="admin" required />
                </div>
                <div class="field">
                    <label class="label" for="password">Password:</label>
                    <input class="input" id="password" name="password" type="password" value="admin" required />
                </div>
                <div class="field">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember"  />
                        <label for="remember">Remember me</label>
                    </div>
                    <input type="submit" value="Log me in" class="button red" />
                </div>
            </form>
        </div>
    </div>
    
    <div class="footer">
        <div class="wrap">
            <span class="float-left"><a href="#">Template Store</a></span>
            <span class="float-right">&copy; Copyright 2013. All rights reserved.</span>
        </div>
    </div>
    
</script>

<script id="index" type="text/html">
    
    <div class="header">
        <div class="wrap">
            <div class="welcome"><a title="" href="#"></a><span>Welcome, Admin!</span></div>
            <div class="menu float-right">
                <ul><li><a href="#logout" title=""><i class="icon icon-3"></i><span>Logout</span></a></li></ul>
            </div>
        </div>
    </div>
    
    <div class="toolbox clearfix">
        <div class="wrap">
            <div class="logo"><img src="img/logo.png" alt="" /></div>
            <div class="buttons">
                <ul>
                    <li><a title="" href="#create-product"><i class="icon product"></i><span>Create product</span></a></li>
                    <!--
                    <li><a title="" href="#create-option"><i class="icon option"></i><span>Create option</span></a></li>
                    <li><a title="" href="#create-discount"><i class="icon discount"></i><span>Create discount</span></a></li>
                    <li><a title="" href="#create-group"><i class="icon group"></i><span>Create group</span></a></li>
                    -->
                    <li><a title="" href="#export"><i class="icon export"></i><span>Email export</span></a><span class="number-middle accounts-total">&nbsp;</span></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="clearfix"></div>
    
    <div class="content clearfix">
        <div class="wrap">
            <div class="menu">
                <ul>
                    <li><a title="" href="#orders"><i class="icon orders"></i><span>Orders</span><span class="number-right orders-total">&nbsp;</span></a></li>
                    <li><a title="" href="#products"><i class="icon products"></i><span>Products</span><span class="number-right products-total">&nbsp;</span></a></li>
                    <li><a title="" href="#groups"><i class="icon groups"></i><span>Groups</span></a></li>
                    <li><a title="" href="#options"><i class="icon options"></i><span>Options</span></a></li>
                    <li><a title="" href="#discounts"><i class="icon discounts"></i><span>Discounts</span></a></li>
                    <li><a title="" href="#history"><i class="icon history"></i><span>History</span></a></li>
                </ul>
            </div>
            <div class="body">
            </div>
        </div>
    </div>
    
    <div class="footer clearfix">
        <div class="wrap">
            <span class="float-left"><a href="#">Template Store</a></span>
            <span class="float-right">&copy; 2013. All rights reserved.</span>
        </div>
    </div>
    
</script>

<script id="orders" type="text/html">
<div class="title"><h5>Manage orders</h5></div>
<div class="stats">
    <ul>
        <li><a title="All orders" class="blue orders-total" href="#orders_total">&nbsp;</a><span>total<br>orders</span></li>
        <li><a title="Pending orders" class="grey orders-waiting" href="#orders_waiting">&nbsp;</a><span>pending<br>orders</span></li>
        <li><a title="Orders on work" class="grey orders-in-work" href="#orders_in_work">&nbsp;</a><span>orders<br>on work</span></li>
        <li class="last"><a title="Completed orders" class="grey orders-done" href="#orders_done">&nbsp;</a><span>completed<br>orders</span></li>
    </ul>
    <div class="clearfix"></div>
</div>
<div class="widget no-margin">
    <div class="head"><h5>All orders</h5></div>
    <table class="table click">
        <thead>
            <tr>
                <th>ID</th>
                <th>Project</th>
                <th>Price</th>
                <th>Status</th>
                <th>Type</th>
                <th>Date</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
    </table>
</div>
</script>

<script id="products" type="text/html">
<div class="title"><h5>Manage products</h5></div>
<div class="widget">
    <div class="head"><h5>All products</h5></div>
    <table class="table click">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Price</th>
                <th>Price / page</th>
                <th>Price / more</th>
                <!-- <th>Date</th> -->
                <th>&nbsp;</th>
            </tr>
        </thead>
    </table>
</div>
<input type="button" value="Create product" class="button create blue" />
</script>

<script id="options" type="text/html">
<div class="title"><h5>Manage options</h5></div>
<div class="widget">
    <div class="head"><h5>All options</h5></div>
    <table class="table click">
        <thead>
            <tr>
                <th>ID</th>
                <th>Active</th>
                <th>Name</th>
                <th>Price</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
    </table>
</div>
<input type="button" value="Create option" class="button create blue" />
</script>

<script id="discounts" type="text/html">
<div class="title"><h5>Manage discounts</h5></div>
<div class="widget">
    <div class="head"><h5>All discounts</h5></div>
    <table class="table click">
        <thead>
            <tr>
                <th>ID</th>
                <th>Active</th>
                <th>Destination</th>
                <th>Discount</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
    </table>
</div>
<input type="button" value="Create discount" class="button create blue" />
</script>

<script id="groups" type="text/html">
<div class="title"><h5>Manage groups</h5></div>
<div class="widget">
    <div class="head"><h5>All groups</h5></div>
    <table class="table click">
        <thead>
            <tr>
                <th>ID</th>
                <th>Active</th>
                <th>Name</th>
                <th>Label</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
    </table>
</div>
<input type="button" value="Create group" class="button create blue" />
</script>

<script id="history" type="text/html">
<div class="title"><h5>View history</h5></div>
<div class="widget">
    <div class="head"><h5>All events</h5></div>
    <table class="table">
        <thead>
            <tr>
                <th>Event</th>
                <th>User</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>
</div>
</script>

<script id="export" type="text/html">
<div class="title"><h5>Email database export</h5></div>
<blockquote>
This wizard will help you to export email database into a text file. <a href="{{urlExport}}">Start export</a>.
</blockquote>
</script>

<script id="manage_group" type="text/html">
<form class="popup form" action="#" method="POST">
    <div class="widget no-margin clearfix">
        {{^id}}<div class="head"><h5 class="block-center center">Create group</h5></div>{{/id}}
        {{#id}}<div class="head"><h5 class="block-center center">Edit group</h5></div>{{/id}}
        {{#id}}<input type="hidden" name="id" value="{{id}}" />{{/id}}
        <div class="field no-border">
            <label class="label" for="name">Name:</label>
            <input class="input" id="name" name="name" type="text" value="{{name}}" />
        </div>
        <div class="field">
            <label class="label" for="type">Type:</label>
            <select class="select" id="type" name="type">
                <option value="STANDARD">Standard</option>
                <option value="PREMIUM">Premium</option>
                <option value="NEW">New</option>
            </select>
        </div>
        <div class="field">
            <label class="label" for="enabled">Active:</label>
            <input id="enabled" name="enabled" type="checkbox"{{#enabled}} checked="checked"{{/enabled}} />
        </div>
        <div class="field center">
            <input type="submit" value="Save" class="button save blue no-margin" />
            <input type="button" value="Cancel" class="button cancel no-margin" />
        </div>
    </div>
</form>
</script>

<script id="manage_option" type="text/html">
<form class="popup form" action="#" method="POST">
    <div class="widget no-margin clearfix">
        {{^id}}<div class="head"><h5 class="block-center center">Create option</h5></div>{{/id}}
        {{#id}}<div class="head"><h5 class="block-center center">Edit option</h5></div>{{/id}}
        {{#id}}<input type="hidden" name="id" value="{{id}}" />{{/id}}
        <div class="field no-border">
            <label class="label" for="name">Name:</label>
            <input class="input" id="name" name="name" type="text" value="{{name}}" />
        </div>
        <div class="field">
            <label class="label" for="price">Price:</label>
            <input class="input" id="price" name="price" type="text" value="{{price}}" />
        </div>
        <div class="field">
            <label class="label" for="enabled">Active:</label>
            <input id="enabled" name="enabled" type="checkbox"{{#enabled}} checked="checked"{{/enabled}} />
        </div>
        <div class="field center">
            <input type="submit" value="Save" class="button save blue no-margin" />
            <input type="button" value="Cancel" class="button cancel no-margin" />
        </div>
    </div>
</form>
</script>

<script id="manage_discount" type="text/html">
<form class="popup form clearfix" action="#" method="POST">
    <div class="widget no-margin clearfix">
        {{^id}}<div class="head"><h5 class="block-center center">Create discount</h5></div>{{/id}}
        {{#id}}<div class="head"><h5 class="block-center center">Edit discount</h5></div>{{/id}}
        {{#id}}<input type="hidden" name="id" value="{{id}}" />{{/id}}
        <div class="field no-border">
            <label class="label" for="target">Destination:</label>
            {{#deletable}}
            <input class="input" id="target" name="target" type="text" value="{{target}}" />
            <br><small>Enter email or product name</small><br clear="all">
            {{/deletable}}
            {{^deletable}}
            <strong>General discount</strong>
            {{/deletable}}
        </div>
        <div class="field">
            <label class="label" for="value">Discount:</label>
            <input class="input" id="value" name="value" type="text" value="{{value}}" />
            <br><small>For example: 5%, 10, 12.75</small><br clear="all">

        </div>
        <div class="field">
            <label class="label" for="enabled">Active:</label>
            <input id="enabled" name="enabled" type="checkbox"{{#enabled}} checked="checked"{{/enabled}} />
        </div>
        <div class="field center">
            <input type="submit" value="Save" class="button save blue no-margin" />
            <input type="button" value="Cancel" class="button cancel no-margin" />
        </div>
    </div>
</form>
</script>

<script id="manage_order" type="text/html">
<form class="form order clearfix" action="#" method="POST">
    <div class="title"><h5>Manage orders</h5></div>
    <div class="widget clearfix">
        <div class="head blue"><h5>{{project_name}}</h5></div>
        <input type="hidden" name="id" value="{{id}}" />
        <div class="half">
            <div class="field no-border">
                <label class="label" for="project_name" style="display: none;">Project name:</label>
                <input class="input" id="project_name" name="project_name" type="text" value="{{project_name}}" />
            </div>
            <div class="field">
                <label class="label" for="project_task" style="display: none;">Requirements:</label>
                <textarea id="project_task" name="project_task" spellcheck="false">{{project_task}}</textarea>
            </div>
            <div class="field upload zip">
                
                <input type="hidden" name="upload_zip" value="" />
                
                <div class="float-left block uploaded"{{^tplPath}} style="display: none;"{{/tplPath}}>
                    <span>template.zip</span>
                    <a href="#delete-file" title="Remove uploaded file"></a>
                </div>
                
                <div class="float-left block empty default"{{#tplPath}} style="display: none;"{{/tplPath}}>
                    <span>File not present</span>
                </div>
                
                <div class="float-left block empty deleted" style="display: none;">
                    <span>** File has been removed</span>
                </div>

                <div class="float-left block progress" style="display: none;">
                    <div>
                        <span class="one">Upload in progress</span>
                        <span class="two">Upload in progress</span>
                    </div>
                </div>
                
                <div class="float-left button gray no-margin">
                    <span>Upload ...</span>
                    <input type="file" />
                </div>

            </div>
        </div>
        <div class="half">
            <table width="100%" cellspacing="0" cellpadding="0" class="table">
                <tbody>
                    <tr class="price no-border">
                    {{#price:label}}<td class="bold green center">${{price:label}}</td>{{/price:label}}
                    {{^price:label}}<td class="bold grey center">$0.00</td>{{/price:label}}
                    {{#state_wait}}<td class="state center"><input type="button" value="Put on work" data-state="WORK" class="button accept sea no-margin" /></td>{{/state_wait}}
                    {{#state_paid}}<td class="state center"><input type="button" value="Put on work" data-state="WORK" class="button accept sea no-margin" /></td>{{/state_paid}}
                    {{#state_work}}<td class="state center"><input type="button" value="Complete order" data-state="DONE" class="button accept sea no-margin" /></td>{{/state_work}}
                    {{#state_done}}<td class="state center"><input type="button" value="Proceed work" data-state="WORK" class="button accept sea no-margin" /></td>{{/state_done}}
                    </tr>
                    <tr>
                        <td>Customer:</td>
                        <td>{{email}}</td>
                    </tr>
                    <tr>
                        <td>Product:</td>
                        <td>{{product_name}}<!-- (ID: {{product_id}}) --></td>
                    </tr>
                    <tr>
                        <td width="40%">Order type:</td>
                        <td>{{{type:label}}}</td>
                    </tr>
                    <tr>
                        <td>Order status:</td>
                        <td>
                            <div class="state-current">{{{status:label}}}</div>
                            <div class="state-work blue hidden" title="Please save changes to apply status">** On work</div>
                            <div class="state-done grey hidden" title="Please save changes to apply status">** Completed</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Order date:</td>
                        <td>{{created:label}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        {{#options.length}}
        <div class="field options clearfix">
            <label>Options: </label><strong>{{options}}</strong>
        </div>
        {{/options.length}}
        <div class="field buttons">
            <input type="submit" value="Save" class="button save blue no-margin" />
            <input type="button" value="Cancel" class="button cancel no-margin" />
            <input type="button" value="Remove" class="button delete red no-margin float-right" />
        </div>
    </div>
</form>
</script>

<script id="manage_product" type="text/html">
<form class="form product clearfix" action="#" method="POST">
    <div class="title"><h5>Manage products</h5></div>
    <div class="widget clearfix">
        <div class="head blue"><h5>{{name}}</h5>{{#id.length}}<h5 class="float-right">#{{id}}</h5>{{/id.length}}</div>
        <input type="hidden" name="id" value="{{id}}" />
        <div class="half one">
            <div class="field no-top-border">
                <label class="label" for="name">Name:</label>
                <input class="input" id="name" name="name" type="text" value="{{name}}" />
            </div>
            <div class="field price">
                <label class="label" for="base_price">Price:</label>
                <input class="input green" id="base_price" name="base_price" type="text" value="{{base_price}}" title="Base price" /><span class="spacer">-</span>
                <input class="input green" id="page_price" name="page_price" type="text" value="{{page_price}}" title="Price per page" /><span class="spacer">-</span>
                <input class="input green no-margin" id="extra_price" name="extra_price" type="text" value="{{extra_price}}" title="Price for redesing" />
            </div>
            <div class="field">
                <label class="label" for="options">Options:</label>
                <input class="input" id="options" name="options" type="text" value="{{options}}" />
            </div>
            <div class="field">
                {{#deletable}}
                <label class="label" for="groups">Groups:</label>
                <input class="input" id="groups" name="groups" type="text" value="{{groups}}" />
                {{/deletable}}
                {{^deletable}}
                <label class="label" for="groups">Groups:</label>
                <strong>Is displayed in all groups</strong>
                {{/deletable}}
            </div>
            {{#deletable}}
            <div class="field upload zip">
                <input type="hidden" name="upload_zip" value="" />
                <div class="float-left block uploaded"{{^tplPath}} style="display: none;"{{/tplPath}}>
                    <span>template.zip</span>
                    <a href="#delete-file" title="Remove uploaded file"></a>
                </div>
                <div class="float-left block empty default"{{#tplPath}} style="display: none;"{{/tplPath}}>
                    <span>File not present</span>
                </div>
                <div class="float-left block empty deleted" style="display: none;">
                    <span>** File has been removed</span>
                </div>
                <div class="float-left block progress" style="display: none;">
                    <div>
                        <span class="one">Upload in progress</span>
                        <span class="two">Upload in progress</span>
                    </div>
                </div>
                <div class="float-left button gray no-margin">
                    <span>Upload ...</span>
                    <input type="file" />
                </div>
            </div>
            {{/deletable}}
        </div>
        <div class="half two">
            <div class="screenshot">
                {{#imgPath.length}}<img src="cat/{{imgPath}}" />{{/imgPath.length}}
            </div>
            {{#deletable}}
            <div class="field upload img no-right-border">
                <input type="hidden" name="upload_img" value="" />
                <div class="float-left block uploaded"{{^imgPath}} style="display: none;"{{/imgPath}}>
                    <span>screen.png</span>
                    <a href="#delete-file" title="Remove uploaded file"></a>
                </div>
                <div class="float-left block empty default"{{#imgPath}} style="display: none;"{{/imgPath}}>
                    <span>Screenshot not present</span>
                </div>
                <div class="float-left block empty deleted" style="display: none;">
                    <span>** Screenshot has been removed</span>
                </div>
                <div class="float-left block progress" style="display: none;">
                    <div>
                        <span class="one">Upload in progress</span>
                        <span class="two">Upload in progress</span>
                    </div>
                </div>
                <div class="float-left button gray no-margin">
                    <span>Upload ...</span>
                    <input type="file" />
                </div>
            </div>
            {{/deletable}}
        </div>
        <div class="field buttons">
            <input type="submit" value="Save" class="button save blue no-margin" />
            <input type="button" value="Cancel" class="button cancel no-margin" />
            {{#deletable}}<input type="button" value="Remove" class="button delete red no-margin float-right" />{{/deletable}}
            {{^deletable}}<input type="button" value="Remove" class="button no-margin float-right" disabled="disabled"/>{{/deletable}}
        </div>
    </div>
</form>
</script>