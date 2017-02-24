<script id="index" type="text/html">
<div class="wrap">
    <div class="head clearfix">
        <div class="main-adress"><a href="#">www.skinshop.net</a></div>
        <div class="shop-adress"><a href="#">www.store.skinshop.net</a></div>
        <div class="logo"><a href="#"><img src="img/logo.png" alt="logo" /></a></div>
        <div class="menu">
            <ul class="groups">
                <li><a href="#" data-group-id="">All designs</a></li>
                {{#groups}}
                <li><a href="#" data-group-id="group-{{id}}">{{name}}</a></li>
                {{/groups}}
            </ul>
        </div>
        <div class="logo-1"></div>
        <div class="logo-2"></div>
    </div>
    <div class="main clearfix">
        {{#products}}
        <div class="site{{classes}}{{#custom}} order-new{{/custom}}">
            <div class="site-img-wrap">
                <div class="site-line-top"></div>
                {{#custom}}<div class="price"><span>from</span>${{price}}</div>{{/custom}}
                {{#custom}}<a href="#order-custom" data-product-id="{{id}}" class="lens popup-link"><span></span></a><img src="img/site_new_order.jpg" alt="site" />{{/custom}}
                {{^custom}}<a href="#preview" data-product-id="{{id}}" class="lens popup-link"><span></span></a><img src="cat/{{thumbPath}}" alt="site" />{{/custom}}
                <div class="site-line-bottom"></div>
            </div>
            <div class="site-info-wrap">
                {{^custom}}
                {{#types}}
                    {{#new}}<span class="type-new"></span>{{/new}}
                    {{#premium}}<span class="type-premium"></span>{{/premium}}
                    {{^premium}}<span class="type-standard"></span>{{/premium}}
                {{/types}}
                {{/custom}}
                {{#custom}}
                <h2>Order New Design</h2>
                {{/custom}}
                <ul class="site-options clearfix">
                    {{#features}}
                    <li><span class="check"></span>{{.}}</li>
                    {{/features}}
                </ul>
                {{^custom}}
                <a href="#preview" data-product-id="{{id}}" class="more-info popup-link">More info...</a>
                <div class="buy-btn{{#discounted_price}} discount{{/discounted_price}}">
                    <span class="price discount-price">${{discounted_price}}</span>
                    <span class="price standard-price">${{price}}</span>
                    <a href="#order-normal" data-product-id="{{id}}" class="popup-link">BUY</a>
                </div>
                {{/custom}}
                {{#custom}}
                <div class="buy-btn"><a href="#order-custom" data-product-id="{{id}}" class="popup-link">BUY NOW!</a></div>
                {{/custom}}
            </div>
        </div>
        {{/products}}
    </div>
</div>
</script>

<script id="preview" type="text/html">
<div class="box-modal box-preview clearfix">
    <div class="arcticmodal-close"></div>
    <div class="site-image"><img src="cat/{{imagePath}}" alt="site" /></div>
    <div class="site-info">
        <div class="header">
            <h3>{{name}}</h3>
            {{#types}}
                {{#new}}<span class="type-new"></span>{{/new}}
                {{#premium}}<span class="type-premium"></span>{{/premium}}
                {{^premium}}<span class="type-standard"></span>{{/premium}}
            {{/types}}
        </div>
        <div class="order">
            <ul class="site-options clearfix">
                {{#features}}
                <li><span class="check"></span>{{.}}</li>
                {{/features}}
            </ul>
            <div class="buy-btn{{#discounted_price}} discount{{/discounted_price}}">
                <span class="price discount-price">${{discounted_price}}</span>
                <span class="price standard-price">${{price}}</span>
                <a href="#order-normal" data-product-id="{{id}}" class="popup-link">ORDER NOW</a>
            </div>
        </div>
        <div class="notes">
            <ul class="clearfix">
                <li><a href="#terms-of-use" class="popup-link">Terms of use</a></li>
                <li><a href="#all-examples" class="popup-link">All examples</a></li>
                <li><a href="#how-it-works" class="popup-link">How It Works</a></li>
            </ul>
            <p>For any questions regarding the purchase, <br>please <a href="#" onclick="return false;">read FAQ</a> or contact <a href="#" onclick="return false;">our support.</a></p>
        </div>
    </div>
</div>
</script>

<script id="order-1" type="text/html">
<div class="box-modal box-order box-order-1 clearfix">
    <div class="arcticmodal-close"></div>
    <div class="box-header"><h2>ORDER INFO</h2></div>
    <form action="" method="GET">
        <input type="hidden" name="type" value="custom">
        <ul class="order-type clearfix">
            <li>
                <div class="pay">
                    <input value="download" id="type-download" name="type" type="radio"{{#download}} checked{{/download}}>Instant download
                    <label for="type-download">&nbsp;</label>
                </div>
                <p>You will received all psd's ad well as script files in a .zip to your email INSTANTLY after payment.</p>
            </li>
            <li>
                <div class="pay">
                    <input value="redesign" id="type-redesign" name="type" type="radio"{{#redesign}} checked{{/redesign}}>Order redesign
                    <label for="type-redesign">&nbsp;</label>
                </div>
                <p>You will received all psd's ad well as script files in a .zip to your email INSTANTLY after payment.</p>
            </li>
        </ul>
        <div class="order-type clear"></div>
        <div class="order-type split"></div>
        <span class="title">Contact Information:</span>
        <table>
            <tr class="field">
                <td><label for="name">Your name:</label></td>
                <td><input id="name" name="name" type="text" class="required input" value="{{name}}"></td>
            </tr>
            <tr class="field">
                <td><label for="email">Your e-mail:</label></td>
                <td><input id="email" name="email" type="email" name="email" class="required input" value="{{email}}"></td>
            </tr>
            <tr class="field">
                <td><label for="messenger">Yahoo or ICQ:</label></td>
                <td><input id="messenger" name="messenger" type="text" name="messenger" class="required input" value="{{messenger}}"></td>
            </tr>
        </table>
        <div class="clear"></div>
        <div class="split"></div>
        <div class="order-btn"><input type="submit" value="NEXT STEP" class="btn"></div>
        <div class="help-text">For any questions regarding the purchase, <br>please <a href="#" onclick="return false;">read FAQ</a> or contact <a href="#" onclick="return false;">our support.</a></div>
    </form>
</div>
</script>

<script id="order-2" type="text/html">
<div class="box-modal box-order box-order-2 clearfix">
    <div class="arcticmodal-close"></div>
    <div class="box-header">
        <h2>ORDER DETAILS</h2>
        <span class="warning">Be careful, this information will be used for changes!</span>
    </div>
    <div class="wishes-form">
        <form action="" method="GET">
            <table>
                <tr class="field">
                    <td><label for="project_name">Project name:</label></td>
                    <td><input id="project_name" name="project_name" type="text" class="input required" value="{{project_name}}" placeholder="Desired project name"></td>
                </tr>
                <tr class="field">
                    <td><label for="project_task">Requirements:</label></td>
                    <td><textarea id="project_task" name="project_task" class="input plans required" placeholder="Your project requirements">{{project_task}}</textarea></td>
                </tr>
                <tr class="field">
                    <td><label for="pages">Inner pages:</label></td>
                    <td>
                        <select id="pages" name="pages" class="input select">
                            <option value="0">Home page only</option>
                            <option value="1">1 inner page</option>
                            <option value="2">2 inner pages</option>
                            <option value="3">3 inner pages</option>
                            <option value="4">4 inner pages</option>
                            <option value="5">5 inner pages</option>
                            <option value="6">6 inner pages</option>
                            <option value="7">7 inner pages</option>
                            <option value="8">8 inner pages</option>
                            <option value="9">9 inner pages</option>
                            <option value="10">10 inner pages</option>
                        </select>
                    </td>
                </tr>
                <tr class="field">
                    <td><label>&nbsp;</label></td>
                    <td>
                        <ul class="options clearfix">
                        {{#features}}
                            {{#included}}
                            <li>
                                <input id="option-{{id}}" type="checkbox" checked disabled>
                                <label for="option-{{id}}">{{name}} - <span>INCLUDED</span></label>
                            </li>
                            {{/included}}
                            {{^included}}
                            <li>
                                <input id="option-{{id}}" name="options[]" value="{{id}}" type="checkbox" {{#selected}}checked{{/selected}}>
                                <label for="option-{{id}}">{{name}} - <span>${{price}}</span></label>
                            </li>
                            {{/included}}
                        {{/features}}
                        </ul>
                    </td>
                </tr>
            </table>
            <div class="split"></div>
            <ul class="total {{#discounted_price}} discount{{/discounted_price}}">
                <li>Total amount:</li>
                <li>
                    <b class="price discount-price">${{discounted_price}}</b>
                    <b class="price standard-price">${{price}}</b>
                </li>
            </ul>
            <div class="clear"></div>
            <div class="split"></div>
            <div class="order-btn"><input type="submit" value="ORDER" class="btn"></div>
            <div class="help-text">For any questions regarding the purchase, <br>please <a href="#" onclick="return false;">read FAQ</a> or contact <a href="#" onclick="return false;">our support.</a></div>
        </form>
    </div>
</div>
</script>

<script id="order-3" type="text/html">
<div class="box-modal box-order box-order-3 clearfix">
    <div class="arcticmodal-close"></div>
    <div class="box-header"><h2>CONFIRM ORDER</h2></div>
    <form action="" method="GET">
    <span class="title">Payment Processor:</span>
    <div class="pay-type">
        <ul class="clearfix">
            <li class="pay-pm"><input value="pmusd" id="currency-pm" name="currency" type="radio" checked><label for="currency-pm">&nbsp;</label></li>
            <li class="pay-eg"><input value="egusd" id="currency-eg" name="currency" type="radio"><label for="currency-eg">&nbsp;</label></li>
            <li class="pay-st"><input value="stusd" id="currency-st" name="currency" type="radio"><label for="currency-st">&nbsp;</label></li>
            <li class="pay-bc"><input value="bcusd" id="currency-bc" name="currency" type="radio"><label for="currency-bc">&nbsp;</label></li>
        </ul>
    </div>
    <div class="split"></div>
    <ul class="total {{#discounted_price}} discount{{/discounted_price}}">
        <li>{{#custom}}50% {{/custom}}{{^custom}}Total {{/custom}}amount:</li>
        <li>
            <b class="price discount-price">${{discounted_price}}</b>
            <b class="price standard-price">${{price}}</b>
        </li>
    </ul>
    <div class="clear"></div>
    <div class="split"></div>
    <div class="order-btn"><input type="submit" value="CONFIRM" class="btn"></div>
    </form>
    <div class="help-text">For any questions regarding the purchase, <br>please <a href="#" onclick="return false;">read FAQ</a> or contact <a href="#" onclick="return false;">our support.</a></div>
</div>
</script>

<script id="all-examples" type="text/html">
<div class="box-modal box-examples clearfix">
    <div class="arcticmodal-close"></div>
    <div class="box-header"><h2>ALL EXAMPLES</h2></div>
    <div class="examples">
        <ul class="tabs clearfix">
            <li><a href="#tab-1" class="select">banners</a></li>
            <li><a href="#tab-2">sliders</a></li>
            <li><a href="#tab-3">inner pages</a></li>
            <li><a href="#tab-4">logotypes</a></li>
        </ul>
        <div id="tab-1" class="page" style="display: block;">
            <div class="banner-info">
                <h4>This is a sample banner size 728x90px</h4>
                <img src="img/examples/728x90.gif" alt="banner" />
            </div>
            <div class="banner-info">
                <h4>This is a sample banner size 468x90px</h4>
                <img src="img/examples/468x60.gif" alt="banner" />
            </div>
            <div class="banner-info">
                <h4>This is a sample banner size 125x125px</h4>
                <img src="img/examples/125x125.gif" alt="banner" />
            </div>
        </div>
        <div id="tab-2" class="page" style="display: none;">
            <h4>This is an example of slider</h4>
            <div class="flash clearfix">
                <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="960" height="385" id="index" align="middle">
                    <param name="allowScriptAccess" value="sameDomain" />
                    <param name="allowFullScreen" value="false" />
                    <param name="movie" value="flash/index.swf" />
                    <param name="quality" value="high" />
                    <param name="bgcolor" value="#000000" />
                    <param name="wmode" value="opaque" />
                    <embed src="img/examples/bn_5.swf" quality="high" bgcolor="#000000" width="725px" height="292" name="index" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="opaque"/>
                </object>
            </div>
        </div>
        <div id="tab-3" class="page" style="display: none;">
            <span class="title">Make Deposit</span>
            <img src="img/examples/js_inner1.jpg" alt="inner" />
            <span class="title">Registration</span>
            <img src="img/examples/js_inner2.jpg" alt="inner" />
            <span class="title">User Area</span>
            <img src="img/examples/js_inner3.jpg" alt="inner" />
        </div>
        <div id="tab-4" class="page" style="display: none;">
            <img src="img/examples/logos.png" alt="logos" />
        </div>
        <script>
            $('.box-examples .tabs li a').click(function() {
                
                var link = $(this), id = link.attr('href'),
                    page = $('.box-examples ' + id);
                
                $('.box-examples .tabs li a').removeClass('select');
                link.addClass('select');
                page.show().siblings('.page').hide();     
                
                return false;
            });
        </script>
    </div>
</div>
</script>

<script id="how-it-works" type="text/html">
<div class="box-modal box-wide box-how-it-works clearfix">
    <div class="arcticmodal-close"></div>
    <div class="box-header"><h2>HOW IT WORKS</h2></div>
    <div class="box-content">
        <h3>How it works?</h3>
        <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan </p>
        <h3>What do you get?</h3>
        <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit. Class aptent </p>
        <h3>How to order?</h3>
        <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit. Class aptent </p>
    </div>
</div>
</script>

<script id="terms-of-use" type="text/html">
<div class="box-modal box-wide box-terms-of-use clearfix">
    <div class="arcticmodal-close"></div>
    <div class="box-header"><h2>TERMS OF USE</h2></div>
    <div class="box-content">
        <h3>Prevents resell templates!</h3>
        <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Mauris in erat justo. Nullam ac urna eu felis dapibus condimentum sit amet a augue. Sed non neque elit. Sed ut imperdiet nisi. Proin condimentum fermentum nunc. Etiam pharetra, erat sed fermentum feugiat, velit mauris egestas quam, ut aliquam massa nisl quis neque. Suspendisse in orci enim.</p>
        <h3>Acquiring a script by itself!</h3>
        <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit. Class aptent </p>
        <h3>Sources in PSD format are not available!</h3>
        <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit. Class aptent </p>
    </div>
</div>
</script>

<script id="payment-success" type="text/html">
<div class="box-modal box-wide box-payment-success clearfix">
    <div class="arcticmodal-close"></div>
    <div class="box-header"><h2>PAYMENT SUCCESSFUL</h2></div>
    <div class="box-content">
        <h3>We have received your payment!</h3>
        <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Mauris in erat justo. Nullam ac urna eu felis dapibus condimentum sit amet a augue. Sed non neque elit. Sed ut imperdiet nisi. Proin condimentum fermentum nunc. Etiam pharetra, erat sed fermentum feugiat, velit mauris egestas quam, ut aliquam massa nisl quis neque. Suspendisse in orci enim.</p>
    </div>
</div>
</script>

<script id="payment-failure" type="text/html">
<div class="box-modal box-wide box-payment-failure clearfix">
    <div class="arcticmodal-close"></div>
    <div class="box-header"><h2>PAYMENT CANCELLED</h2></div>
    <div class="box-content">
        <h3>We have not received your payment!</h3>
        <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Mauris in erat justo. Nullam ac urna eu felis dapibus condimentum sit amet a augue. Sed non neque elit. Sed ut imperdiet nisi. Proin condimentum fermentum nunc. Etiam pharetra, erat sed fermentum feugiat, velit mauris egestas quam, ut aliquam massa nisl quis neque. Suspendisse in orci enim.</p>
    </div>
</div>
</script>

<script id="bitcoin-form" type="text/html">
    <div class="bitcoin-form">
        <div class="message">Send <b>{{amount}} BTC</b> to <b>{{address}}</b></div>
        <div class="comment">Once we receive your payment, you will be instantly notified.</div>
        <div class="progress-bar"><span style="width: 0%"></span></div>
    </div>
</script>
