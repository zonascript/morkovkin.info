;(function ($, window, document, undefined)
{
    var app = null;
    var pluginName = "app";
    
    var defaults = 
    {
        debug: false,
        products: [],
        groups: [],
        options: [],
        urlOrder: false,
        urlStatus: false
    };
    
    $.fn[pluginName] = function (options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new App(this, options));
            }
        });
    };
    
    function App(element, options) {
        app = this;
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }
    
    App.prototype.init = function()
    {
        // Create order instance
        app.order = new Order();
        
        // Debug info
        if (app.settings.debug)
        {
            console.log(app.settings);
            
            app.order.name = 'John Doe';
            app.order.email = '#';
            app.order.messenger = '';
            app.order.project = 'Custom Order';
            app.order.requirements = 'Order requirements';
        }
        
        // Convert products
        $.each(app.settings.products, function(index, value) {
            app.settings.products[index] = $.extend(new Product(), value);
        });
        
        // Render index template
        $(app.element).mustache('index', app.settings);
        
        // Attach isotope plugin
        $('.main').isotope({
            itemSelector: '.site',
            layoutMode : 'fitRows'
        });
        
        // Attach product filtering
        $('.groups a').click(function() {
            var groupId = $(this).data('group-id');
            var filter = '.site, .order-new';
            if (groupId !== '') filter = '.' + groupId + ', .order-new';
            $('.main').isotope({filter: filter});
            return false;
        });
        
        // Preview popups
        $('a[href=#preview]').click(function() {
            
            var id = $(this).data('product-id'), data = app.getProductById(id);
            var popup = $('<div>').mustache('preview', data).arcticmodal();
            
            popup.find('a[href=#order-normal]').click(function() {
                $.arcticmodal('close'); app.startNewOrder($(this).data('product-id')); return false;
            });
            
            popup.find('a[href=#terms-of-use]').click(function() {$('<div>').mustache('terms-of-use').arcticmodal(); return false;});
            popup.find('a[href=#all-examples]').click(function() {$('<div>').mustache('all-examples').arcticmodal(); return false;});
            popup.find('a[href=#how-it-works]').click(function() {$('<div>').mustache('how-it-works').arcticmodal(); return false;});
            
            return false;
        });
        
        // Order custom template
        $('a[href=#order-custom]').click(function() {
            app.startNewOrder($(this).data('product-id')); return false;
        });
        
        // Order normal (download or redesign)
        $('a[href=#order-normal]').click(function() {
            app.startNewOrder($(this).data('product-id')); return false;
        });
        
        // Successful payment popups
        if (window.location.hash === '#success')
            $('<div>').mustache('payment-success', this).arcticmodal({openEffect: {type: 'none'}});

        // Failed payment popups
        if (window.location.hash === '#failure')
            $('<div>').mustache('payment-failure', this).arcticmodal({openEffect: {type: 'none'}});
        
    };
    
    App.prototype.getProductById = function(product_id)
    {
        for (var i = 0; i < app.settings.products.length; i++)
            if (app.settings.products[i]['id'] == product_id)
                return app.settings.products[i];
        
        return null;
    };
    
    App.prototype.startNewOrder = function(product_id)
    {
        app.order.product_id = product_id;
        app.order.product = app.getProductById(product_id);
        app.order.type = app.order.product.custom() ? 'custom' : 'normal';
        app.order.showTemplateInfo();
    };
    
    
    /***********************************
    * Product class
    ***********************************/
    
    function Product()
    {
        this.id = null;
        this.base_price = 0.00;
        this.page_price = 0.00;
        this.discounted_price = false;
        this.options = '';
        this.groups = '';
    };
    
    Product.prototype.price = function()
    {
        return parseInt(this.base_price);
    };
    
    Product.prototype.custom = function()
    {
        return this.type == 'CUSTOM';
    };
    
    Product.prototype.classes = function()
    {
        var result = ' id-' + this.id;
        if (this.groups) result += ' group-' + this.groups.split(',').join(' group-');
        if (this.options) result += ' option-' + this.options.split(',').join(' option-');
        return result;
    };
    
    Product.prototype.types = function()
    {
        var groupIds = this.groups.split(','),
            groups = app.settings.groups,
            types = {};
        
        for (var i = 0; i < groups.length; i++)
            if ($.inArray(groups[i]['id'], groupIds))
                types[groups[i]['type'].toLowerCase()] = true;
        
        return this.types = types;
    };
    
    Product.prototype['features'] = function()
    {
        if (!this.options) return []; var list = [], find = this.options.split(',');
        $.each(app.settings.options, function(index, value) {
            if ($.inArray(value['id'], find) != -1)
                list.push(value['name']);
        });
        return list;
    };
    
    
    /***********************************
    * Order class
    ***********************************/
    
    function Order()
    {
        this.product_id = null;
        this.product = null;
        this.type = 'custom';
        this.name = '';
        this.email = '';
        this.messenger = '';
        this.project_name = '';
        this.project_task = '';
        this.pages = 0;
        this.options = [];
        this.price = 0.00;
        this.discounted_price = null;
        this.currency = null;
    };
    
    Order.prototype.download = function()
    {
        return this.type === 'download';
    };
    
    Order.prototype.redesign = function()
    {
        return this.type === 'redesign';
    };
    
    Order.prototype.custom = function()
    {
        return this.type === 'custom';
    };
    
    Order.prototype.features = function()
    {
        var includedOptions = this.product.options.split(','),
            selectedOptions = this.options,
            features = app.settings.options.slice();
        
        for (var i = 0; i < features.length; i++)
        {
            features[i]['included'] = $.inArray(features[i]['id'], includedOptions) != -1;
            features[i]['selected'] = $.inArray(features[i]['id'], selectedOptions) != -1;
        }
        
        return features;
    };
    
    Order.prototype.updatePrice = function(popup)
    {
        var self = this, result = $.ajax
        ({
            url: app.settings.urlOrder,
            type: 'POST', dataType: 'json', data:
            {
                calculate: true,
                product_id: self.product_id,
                type: self.type,
                email: self.email,
                pages: self.pages,
                options: self.options.join(',')
            }
        }).done(function(data) {
            
            var divider = (popup.find('.box-order-3').length > 0 && self.type === 'custom') ? 2 : 1;
            
            self.price = parseFloat(data['price']) / divider;
            self.discounted_price = parseFloat(data['discounted_price']) / divider;

            popup.find('.total .price').text('$' + parseInt(self.price));
            popup.find('.total .discount-price').text('$' + parseInt(self.discounted_price));
            
            if (self.price !== self.discounted_price)
                popup.find('.total').addClass('discount');
            else
                popup.find('.total').removeClass('discount');
            
            
        });
    };
    
    Order.prototype.showTemplateInfo = function()
    {
        var popup = $('<div>').mustache('order-1', this).arcticmodal(),
            form = popup.find('form'), self = this;
            
        // Order type switching
        popup.find('.payments li .pay').click(function()
        {
            $(this).closest('.payments').find('.pay').removeClass('highlight');
            $(this).addClass('highlight').find('input[type=radio]').prop("checked", "checked");
            return false;
        });
        
        // Select "instant download"
        popup.find('.order-type li:first-child .pay label').click();
        
        // Remove order type tabs
        if (this.type === 'custom')
            popup.find('.order-type').remove();
        
        // Validate and save input
        form.submit(function()
        {
            if (form.validate().form())
            {
                if ((type = form.find('.pay :checked').val()) !== undefined)
                    self['type'] = form.find('.pay :checked').val();
                
                self['name'] = form.find('#name').val();
                self['email'] = form.find('#email').val();
                self['messenger'] = form.find('#messenger').val();
                
                $.arcticmodal('close');
                
                if (self.type === 'download')
                    self.showTemplatePay();
                else
                    self.showTemplateWishes();
            }
            
            return false;
        });
        
    };
    
    Order.prototype.showTemplateWishes = function()
    {
        var popup = $('<div>').mustache('order-2', this).arcticmodal(),
            form = popup.find('form'), self = this;
            
        // Update price on load
        self.updatePrice(popup);
        
        // Update price on change
        popup.find('.options li input').change(function() {
            var checkbox = $(this), id = checkbox.attr('value');
            var index = self.options.indexOf(id);
            if (index >= 0) self.options.splice(index, 1);
            if (checkbox.is(':checked')) self.options.push(id);
            self.updatePrice(popup);
        });
        
        // Update price on pages change
        popup.find('#pages').change(function() {
            self.pages = $(this).val();
            self.updatePrice(popup);
        });
        
        // Validate and save input
        form.submit(function()
        {
            if (form.validate().form())
            {
                self['project_name'] = form.find('#project_name').val();
                self['project_task'] = form.find('#project_task').val();
                self['pages'] = form.find('#pages').val();
                
                $.arcticmodal('close');
                
                self.showTemplatePay();
            }
            
            return false;
        });
        
    };
    
    Order.prototype.showTemplatePay = function()
    {
        var popup = $('<div>').mustache('order-3', this).arcticmodal(),
            form = popup.find('form'), self = this;
            
        // Update price on load
        self.updatePrice(popup);
        
        // Validate and save input
        form.submit(function()
        {
            if (form.validate().form())
            {
                self['currency'] = form.find('.pay-type :checked').val();
                
                var result = $.ajax
                ({
                    url: app.settings.urlOrder,
                    type: 'POST', dataType: 'json', data:
                    {
                        product_id: self.product_id,
                        type: self.type,
                        name: self.name,
                        email: self.email,
                        messenger: self.messenger,
                        project_name: self.project_name,
                        project_task: self.project_name,
                        pages: self.pages,
                        options: self.options.join(','),
                        currency: self.currency
                    }
                }).done(function(data) {
                    
                    var order_form = data['payment_form'],
                        order_id = data['order_id'];
                    
                    if (data)
                    {
                        if (self.currency === 'bcusd')
                        {
                            $('.order-btn').empty().mustache('bitcoin-form', order_form);
                            
                            // Order status checker
                            var checker = function()
                            {
                                $('.progress-bar span').css('width', (5 + ((time += 5) * 95) / full) + '%');
                                
                                $.ajax({
                                    url: app.settings.urlStatus,
                                    type: 'GET', dataType: 'json',
                                    data: {order_id: order_id}
                                })
                                .done(function(data) {
                                    if (data['order_status'] === 'PAID')
                                    {
                                        clearInterval(interval);
                                        $.arcticmodal('close');
                                        $('.progress-bar span').css('width', '100%');
                                        $('<div>').mustache('payment-success', this).arcticmodal();
                                    }
                                });
                            };
                            
                            // Order status checking
                            var full = 10 * 60, time = 0,
                                interval = window.setInterval(checker, 5000);
                            
                            // Call checker
                            checker();
                        }
                        else
                        {
                            $('.order-btn .btn').attr('disabled', 'disabled');
                            $(order_form).appendTo('body').submit();
                        }
                    }
                    
                });
            }
            
            return false;
        });
        

    };
    
})(jQuery, window, document);