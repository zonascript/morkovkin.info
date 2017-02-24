;(function ($, window, document, undefined)
{
    var app = null;
    var pluginName = "app";
    
    var defaults = 
    {
        debug: false,
        urlLogin: null,
        urlLogout: null,
        urlStats: null,
        urlOrderList: null,
        urlProductList: null,
        urlOptionList: null,
        urlDiscountList: null,
        urlGroupList: null,
        urlHistory: null,
        urlExport: null,
        urlFileUpload: null,
    };
    
    $.fn[pluginName] = function(options)
    {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new App(this, options));
            }
        });
    };
    
    function App(element, options)
    {
        app = this;
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }
    
    App.prototype.init = function()
    {
        // Initialize dataTables classes
        $.extend($.fn.dataTableExt.oStdClasses, {
            sSortAsc: 'sort-asc',
            sSortDesc: 'sort-dsc',
            sSortable: 'sort',
            sPaging: 'page',
            sPageFirst: 'page-first',
            sPagePrevious: 'page-prev',
            sPageNext: 'page-next',
            sPageLast: 'page-last',
            sPageButton: 'page',
            sPageButtonActive: 'page-active',
            sPageButtonStaticDisabled: 'page-disabled',
            sProcessing : 'ajax',
            sRowEmpty : 'empty'
        });
        
        // Initialize dataTables options
        $.extend($.fn.dataTable.defaults, {
            sPaginationType: 'full_numbers',
            sDom: 'rt<"pager"p>',
            fnDrawCallback: function(oSettings )
            {
                $(oSettings.nTable).closest('.context').show().siblings().hide();
                
                $(oSettings.nTable).find('input:checkbox, input:radio').uniform({checkboxClass: 'check'});
                
                if (!oSettings.fnRecordsTotal())
                {
                    $(oSettings.nTableWrapper).find('.pager').remove();
                    $(oSettings.nTableWrapper).find('thead').remove();
                }
            }
        });
        
        // Fix dataTables language
        $.extend($.fn.dataTable.defaults.oLanguage, {
            sProcessing: '&nbsp;',
            sEmptyTable: 'No data to display',
        });
        
        // Initialize jAlerts
        $.alerts.okButton = 'OK';
        $.alerts.cancelButton = 'Cancel';
        $.alerts.overlayColor = '#FFF';
        $.alerts.overlayOpacity = 0.5;
        $.alerts.verticalOffset = -50;
        
        // Initialize SimpleModal
        $.modal.defaults.overlayClose = true;
        $.modal.defaults.focus = true;
        
        // Additional validation for discount value
        $.validator.addMethod('discount-value', function(value, element, regexp) {
            return /^(\d+(\.\d{1,2})?|\d+\%)$/.test(value);}, 'Invalid discount'
        );
        
        // Additional validation for discount target
        $.validator.addMethod('discount-target', function(value, element, regexp)
        {
            if (jQuery.validator.methods['email'].call(this, value, element)) return true;
            var result = false; $.ajax({url: app.settings.urlDiscountList, type: 'POST',
                data: {target: value}, dataType: 'json', async: false,
                success: function(data) {result = data.result;}});
            return result;
        }, 'Invalid destination');
        
        // Initialize MomentJS
        app.moment = moment.lang('en');
        
        // Show login or index
        if (app.settings.login)
            app.actionIndex();
        else
            app.actionLogin(false);
    };
    
    App.prototype.getContext = function(id, settings, replace)
    {
        var postfix = id.replace(/_/g, '-'),
            context = $('.body .context-' + postfix), 
            className = 'context context-' + postfix;
        
        // Mark replacable context
        if (replace) className += ' replace';
        
        // Need to remove existing context
        if (replace) context.remove();
        
        // Remove other replecable contexts
        $('.body .context.replace').remove();
        
        // Create context if was not found
        if (replace || !context.length)
        {
            context = $('<div />').addClass(className).hide().appendTo('.body');
            context.mustache(id, settings);
        }
        
        return context;
    };
    
    App.prototype.actionIndex = function()
    {
        var body = $(app.element).empty().mustache('index', app.settings);
        
        // Statistics
        var updateStats = function()
        {
            $.get(app.settings.urlStats, {}, function(data) {
                // console.log(data);
                $.each(data, function(key, value) {
                    $('.' + key.replace(/_/g, '-')).text(value);
                });
            });
        };
        
        // Logout
        body.find('a[href=#logout]').click(app.actionLogout);
        
        // Toolbox
        body.find('a[href=#create-product]').click(function(e) {app.actionManageProduct(e, {});});
        body.find('a[href=#create-option]').click(function(e) {app.actionManageOption(e, {});});
        body.find('a[href=#create-discount]').click(function(e) {app.actionManageDiscount(e, {});});
        body.find('a[href=#create-group]').click(function(e) {app.actionManageGroup(e, {});});
        body.find('a[href=#export]').click(app.actionExport);
        
        // Menu
        body.find('a[href=#orders]').click(app.actionOrders);
        body.find('a[href=#products]').click(app.actionProducts);
        body.find('a[href=#options]').click(app.actionOptions);
        body.find('a[href=#discounts]').click(app.actionDiscounts);
        body.find('a[href=#groups]').click(app.actionGroups);
        body.find('a[href=#history]').click(app.actionHistory);
        
        // Auto-update stats
        if (!app.settings.debug)
            setInterval(updateStats, 10000);

        // Get stats
        updateStats();

        // Show orders
        app.actionOrders();
    };
    
    App.prototype.actionLogin = function(e, logout)
    {
        var body = $(app.element).empty().mustache('login')
            form = body.find('form'), 
            alertInvalidLogin = body.find('.message-invalid-login'),
            alertLoggedOut = body.find('.message-logged-out');
        
        // Hide alerts
        body.find('.alert').hide();
        
        // Plugins
        $('input:checkbox, input:radio').uniform({checkboxClass: 'check'});
        $('form').validate({errorElement: 'div', onfocusout: false, onsubmit: false});
        
        // Login
        form.submit(function()
        {
            if (form.validate().form())
            {
                $.post(app.settings.urlLogin, form.serialize(), function(data) {
                    if (data.result) app.actionIndex(); else alertInvalidLogin.slideDown('fast');
                });
            }
            return false;
        });
        
        // Logout
        if (logout)
        {
            $.post(app.settings.urlLogout, {}, function(data) {
                if (data.result)
                {
                    alertLoggedOut.slideDown('fast');
                    $.alerts._hide();
                }
            });
        }
        
        return false;
    };
    
    App.prototype.actionLogout = function(e)
    {
        jConfirm('Really log out?', 'System logout', function(confirm)
        {
            if (confirm)
            {
                app.actionLogin(e, true);
                return true; // Do not hide alert - we'll handle this
            }
        });
        
        return false;
    };

    App.prototype.actionExport = function()
    {
        var context = app.getContext('export', app.settings);
        
        // Show this block
        context.show().siblings().hide();
                
        // top.location.href = app.settings.urlExport;
        return false;
    };
    
    App.prototype.actionOrders = function()
    {
        var context = app.getContext('orders', app.settings),
            title = context.find('.head h5'), 
            links = context.find('.stats ul li a'),
            table = context.find('.table');
        
        // Initialize context
        if (!context.data('initialized'))
        {
            // Attach dataTables
            table = context.find('.table').dataTable({
                bProcessing: true,
                bServerSide: true,
                sAjaxSource: app.settings.urlOrderList,
                aoColumns:
                [
                    {mData: 'id', sWidth: 35, sClass: 'center' /*, mRender: function(v) {return ('0000' + v).slice(-3);} */},
                    {mData: 'project_name', sClass: 'blue'},
                    {mData: 'price', sWidth: 50, sClass: 'center', mRender: function(v) {return (v == 0) ? '<span class="grey">$0.00</span>' : '<span class="green">$' + v + '</span>';}},
                    {mData: 'status', sWidth: 75, sClass: 'center', mRender: function(data, type, row) {return row['status:label'];}},
                    {mData: 'type', sWidth: 75, sClass: 'center', mRender: function(data, type, row) {return row['type:label'];}},
                    {mData: 'created', sWidth: 100, sClass: 'center', mRender: function(v) {return moment.unix(v).fromNow();}},
                    {mData: 'id', sWidth: 25, bSortable: false, mRender: function(v) {return '<a class="float-right action delete" href="#delete" title="Remove order">&nbsp;</a>';}}
                ]
            });
            
            // Attach view action
            table.on("click", "tbody tr td a[href=#view]", function() {
                var link = $(this),
                    row = link.closest('tr'),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                
                return false;
            });
            
            // Attach delete action
            table.on("click", "tbody tr td a[href=#delete]", function(e) {
                var link = $(this),
                    row = link.closest('tr'),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                
                // TODO: Возможность удалять без подтверждения, удерживая CTRL.
                jConfirm('Do you want to delete order?', 'Delete order', function(confirm) {
                    if (confirm)
                    {
                        table.fnSettings().sAjaxSource = app.settings.urlOrderList +
                            '?sFilter=' + links.filter('.blue').attr('href').substring(1) + 
                            '&delete=' + data['id'];
                        table.fnSettings().aoDrawCallback.push({"fn": function() {$.alerts._hide();}});
                        table.fnDraw(false);
                        return true; // Do not hide alert - we'll handle this
                    }
                });
                
                return false;
            });
            
            // Attach order management
            table.on("click", "tbody tr", function() {
                
                var row = $(this),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos),
                    changed = false;
                
                data['created:label'] = moment.unix(data.created).fromNow();
                data['price:label'] = data.price > 0 ? data.price : null;
                
                var saveContext = app.getContext('manage_order', data, true),
                    form = saveContext.find('form');
                
                // Plugins
                form.validate({errorElement: 'div', onfocusout: false, onsubmit: false, rules: {
                    project_name: {required: true},
                    project_task: {required: true}
                }});
                
                // File upload management
                form.find('.upload').each(function() {
                    
                    var self = $(this),
                        file = self.find('input[type=file]'),
                        inputAttachment = self.find('input[type=hidden]'),
                        removeLink = self.find('a[href=#delete-file]'),
                        uploaded = self.find('.uploaded'),
                        progress = self.find('.progress'),
                        emptyDefault = self.find('.empty.default'),
                        emptyDeleted = self.find('.empty.deleted'),
                        bar = progress.find('div').css('width', '0%');
                    
                    // Uploading file
                    file.change(function() {
                        
                        // Warn user if there is no files
                        if (file.get(0).files.length == 0)
                            return jAlert('Файлы не выбраны');
                        
                        // Hide upload input and show progress
                        emptyDefault.hide();
                        emptyDeleted.hide();
                        uploaded.hide();
                        progress.show();
                        
                        // Send file
                        $.ajax({
                            url: app.settings.urlFileUpload,
                            processData: false,
                            contentType: false,
                            type: 'POST',
                            xhr: function() {
                                var xhr = new XMLHttpRequest();
                                xhr.upload.addEventListener('progress', function(e) {
                                    bar.css('width', Math.ceil(e.loaded / e.total) * 100 + '%');
                                }, false);
                                return xhr;
                            },
                            data: function() {
                                var formData = new FormData(), 
                                    formFile = file.get(0).files[0];
                                formData.append('upload', formFile);
                                return formData;
                            }(),
                            success: function(data) {
                                progress.hide();
                                uploaded.show();
                                changed = true;
                                inputAttachment.val(data['upload']);
                            }
                        });
                        
                    });
                    
                    // File delete
                    removeLink.click(function() {
                        emptyDeleted.show();
                        emptyDefault.hide();
                        uploaded.hide();
                        progress.hide();
                        changed = true;
                        inputAttachment.val('delete');
                        return false;
                    });
                    
                });
                
                // Form changed state
                saveContext.find('input, textarea').bind('input propertychange change cut paste', function() {changed = true;});
                
                // Change state
                saveContext.find('.state .button').click(function() {
                    var button = $(this),
                        state = button.data('state').toLowerCase(),
                        label = saveContext.find('.state-' + state);
                    
                    changed = true;
                    
                    button.attr('disabled', 'disabled');
                    label.show().siblings().hide();
                    form.append('<input type="hidden" name="state" value="' + state + '" />');
                    
                    return false;
                });
                
                // Save
                form.submit(function() {
                    
                    if (form.validate().form())
                    {
                        saveContext.find('.widget').append('<div class="ajax" />');
                        table.fnSettings().sAjaxSource = app.settings.urlOrderList + '?insert=1&' + form.serialize();
                        table.fnSettings().aoDrawCallback.push({"fn": function() {context.show().siblings().hide(); saveContext.remove();}});
                        table.fnDraw(false);
                    }
                    
                    return false;
                });

                // Cancel
                saveContext.find('.button.cancel').click(function() {
                    if (changed)
                        jConfirm('You have made changes that will be permanently lost. Proceed?', 'Cancel changes', function(confirm) {
                            if (confirm) {context.show().siblings().hide(); form.remove();}
                        });
                    else
                        {context.show().siblings().hide(); form.remove();}
                    return false;
                });
                
                // Delete
                saveContext.find('.button.delete').click(function() {
                    jConfirm('Do you want to delete order?', 'Delete order', function(confirm) {
                        if (confirm)
                        {
                            table.fnSettings().sAjaxSource = app.settings.urlOrderList +
                                '?sFilter=' + links.filter('.blue').attr('href').substring(1) + 
                                '&delete=' + data['id'];
                            table.fnSettings().aoDrawCallback.push({"fn": function() {context.show().siblings().hide(); form.remove(); $.alerts._hide();}});
                            table.fnDraw(false);
                            return true; // Do not hide alert - we'll handle this
                        }
                    });
                    return false;
                });
                
                // Cancel on escape
                $(document).bind('keydown', function(e) {if (e.keyCode === 27) saveContext.find('.button.cancel').click();})
                
                saveContext.show().siblings().hide();
            });
            
            // Attach filtering
            links.click(function() {
                var link = $(this);
                table.fnSettings().sAjaxSource = app.settings.urlOrderList + '?sFilter=' + link.attr('href').substring(1);
                table.fnDraw(true);
                title.text(link.attr('title'));
                links.removeClass('blue').addClass('grey');
                link.removeClass('grey').addClass('blue');
                return false;
            });
            
            // Mark as initialized
            context.data('initialized', true);
        }
        else
            // Show this block
            context.show().siblings().hide();

        return false;
    };
    
    App.prototype.actionProducts = function()
    {
        var context = app.getContext('products', app.settings),
            table = context.find('.table');
        
        // Initialize context
        if (!context.data('initialized'))
        {
            // Attach dataTables
            table = context.find('.table').dataTable({
                bProcessing: true,
                bServerSide: true,
                sAjaxSource: app.settings.urlProductList,
                aoColumns:
                [
                    {mData: 'id', sWidth: 35, sClass: 'center'},
                    //{mData: 'enabled', sWidth: 75, sClass: 'center', mRender: function(v) {return '<input type="checkbox"' + (v == 1 ? ' checked="checked"' : '') + ' disabled="disabled"/>';}},
                    {mData: 'name', sClass: 'blue'},
                    {mData: 'status', sWidth: 100, sClass: 'center', bSortable: false},
                    {mData: 'base_price', sWidth: 75, sClass: 'green center', mRender: function(v) {return (v !== null) ? '$' + v : '-';}},
                    {mData: 'page_price', sWidth: 75, sClass: 'green center', mRender: function(v) {return (v !== null) ? '$' + v : '-';}},
                    {mData: 'extra_price', sWidth: 75, sClass: 'green center', mRender: function(v) {return (v !== null) ? '$' + v : '-';}},
                    // {mData: 'created', sClass: 'center', sWidth: 100, mRender: function(v) {return moment.unix(v).fromNow();}},
                    {mData: 'id', sWidth: 25, sClass: 'center', bSortable: false, mRender: function(v) {if (v == 1) return '&nbsp;'; return '<a class="float-right action delete" href="#delete" title="Удалить опцию">&nbsp;</a>';}}
                ]
            });
            
            // Attach delete action
            table.on("click", "tbody tr td a[href=#delete]", function(e) {
                var link = $(this),
                    row = link.closest('tr'),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                
                // TODO: Возможность удалять без подтверждения, удерживая CTRL.
                jConfirm('Do you want to delete product?', 'Delete product', function(confirm) {
                    if (confirm)
                    {
                        table.fnSettings().sAjaxSource = app.settings.urlProductList + '?delete=' + data['id'];
                        table.fnSettings().aoDrawCallback.push({"fn": function() {$.alerts._hide();}});
                        table.fnDraw(false);
                        return true; // Do not hide alert - we'll handle this
                    }
                });
                
                return false;
            });
            
            // Attach product management
            table.on("click", "tbody tr", function()
            {
                
                var row = $(this),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                    
                context.find('.button.create').trigger('click', data);
            });
            
            // Attach create
            context.find('.button.create').click(function(e, data)
            {
                
                if (!data) data = {
                    name: 'New product',
                    base_price: 100.00,
                    page_price: 25.00,
                    extra_price: 0.00,
                    enabled: true,
                    deletable: true,
                    options: '',
                    groups: ''
                };
                
                var saveContext = app.getContext('manage_product', data, true),
                    form = saveContext.find('form'), changed = false;
                
                // Get lists data
                jQuery.ajax({
                    async: false,
                    url: app.settings.urlProductList + '?lists=1',
                    success: function(response)
                    {
                        var optionsTemplate = '<div class="multiselect options"><div class="dropdown"></div><ul>{{#.}}<li><input type="checkbox" id="option_{{id}}" value="{{id}}" {{#checked}}checked="checked"{{/checked}}><label for="option_{{id}}">{{name}}</label></li>{{/.}}</ul></div>';
                        var groupsTemplate = '<div class="multiselect groups"><div class="dropdown"></div><ul>{{#.}}<li><input type="checkbox" id="group_{{id}}" value="{{id}}" {{#checked}}checked="checked"{{/checked}}><label for="group_{{id}}">{{name}}</label></li>{{/.}}</ul></div>';
                        
                        var selOptions = (data['options'] || '').split(/\s*\,\s*/);
                        var selGroups = (data['groups'] || '').split(/\s*\,\s*/);
                        
                        $.each(response['options'], function(id, row) {row['checked'] = $.inArray(row['id'], selOptions) != -1;});
                        $.each(response['groups'], function(id, row) {row['checked'] = $.inArray(row['id'], selGroups) != -1;});
                        
                        $(Mustache.render(optionsTemplate, response['options'])).insertAfter(saveContext.find('#options').hide());
                        $(Mustache.render(groupsTemplate, response['groups'])).insertAfter(saveContext.find('#groups').hide());
                        
                        $(document).mousedown(function(e) {if ($(e.target).closest('.multiselect').length == 0) saveContext.find('.multiselect ul').hide();});
                        $('.multiselect .dropdown').click(function() {saveContext.find('.multiselect ul').hide(); $(this).next().toggle();});
                        $('.multiselect :checkbox').change(function() {
                            
                            var parent = $(this).closest('.multiselect'),
                                dropdown = parent.find('.dropdown'),
                                checkers = parent.find(':checkbox:checked'),
                                n = checkers.length;
                            
                            var select = [];
                            checkers.each(function(i, check) {
                                select.push($(check).attr('value'));
                            });
                            
                            if (parent.is('.options'))
                            {
                                saveContext.find('#options').val(select.join(', '));
                                if (n === 0) dropdown.text('No options selected');
                                else if (n % 10 === 1) dropdown.text(n + ' option selected');
                                else if (n >= 5 && n <= 20) dropdown.text(checkers.length + ' options selected');
                                else dropdown.text(checkers.length + ' options selected');
                            }
                            
                            if (parent.is('.groups'))
                            {
                                saveContext.find('#groups').val(select.join(', '));
                                if (n === 0) dropdown.text('No groups selected');
                                else if (n % 10 === 1) dropdown.text(n + ' group selected');
                                else if (n >= 5 && n <= 20) dropdown.text(checkers.length + ' groups selected');
                                else dropdown.text(checkers.length + ' groups selected');
                            }
                            
                        }).trigger('change');
                        
                        
                    }
                });
                
                // Plugins
                // form.find('select').val(data['type']).dropkick();
                // form.find('select').dropkick();
                form.find('input:checkbox, input:radio').uniform({checkboxClass: 'check'});
                form.find('.price input').tipsy({fade: true, gravity: 's', trigger: 'focus'}).spinner({});
                form.validate({errorElement: 'div', onfocusout: false, onsubmit: false, rules: {
                    name: {required: true},
                    base_price: {required: true, number: true, min: 10.00},
                    page_price: {required: true, number: true, min: 10.00},
                    extra_price: {required: true, number: true, min: 10.00}
                }, errorPlacement: function(error, element) {
                    var parent = element.parent(".spinner");
                    if (parent.length > 0) error.insertAfter(parent); else error.insertAfter(element);
                }});
                
                // Save
                form.submit(function() {
                    
                    if (form.validate().form())
                    {
                        saveContext.find('.widget').append('<div class="ajax" />');
                        table.fnSettings().sAjaxSource = app.settings.urlProductList + '?insert=1&' + form.serialize();
                        table.fnSettings().aoDrawCallback.push({"fn": function() {context.show().siblings().hide(); saveContext.remove();}});
                        table.fnDraw(false);
                    }
                    
                    return false;
                });
                
                // Cancel
                saveContext.find('.button.cancel').click(function() {
                    if (changed)
                        jConfirm('Your changes will be permanently lost. Continue?', 'Cancel changes', function(confirm) {
                            if (confirm) {context.show().siblings().hide(); form.remove();}
                        });
                    else
                        {context.show().siblings().hide(); form.remove();}
                    return false;
                });
                
                // Delete
                saveContext.find('.button.delete').click(function() {
                    jConfirm('Do you want to delete product?', 'Delete product', function(confirm) {
                        if (confirm)
                        {
                            table.fnSettings().sAjaxSource = app.settings.urlProductList + '?delete=' + data['id'];
                            table.fnSettings().aoDrawCallback.push({"fn": function() {context.show().siblings().hide(); form.remove(); $.alerts._hide();}});
                            table.fnDraw(false);
                            return true; // Do not hide alert - we'll handle this
                        }
                    });
                    return false;
                });
                
                // File upload management
                form.find('.upload').each(function() {
                    
                    var self = $(this),
                        file = self.find('input[type=file]'),
                        inputAttachment = self.find('input[type=hidden]'),
                        removeLink = self.find('a[href=#delete-file]'),
                        uploaded = self.find('.uploaded'),
                        progress = self.find('.progress'),
                        emptyDefault = self.find('.empty.default'),
                        emptyDeleted = self.find('.empty.deleted'),
                        bar = progress.find('div').css('width', '0%');
                    
                    // Uploading file
                    file.change(function() {
                        
                        // Warn user if there is no files
                        if (file.get(0).files.length == 0)
                            return jAlert('No files selected');
                        
                        // Hide upload input and show progress
                        emptyDefault.hide();
                        emptyDeleted.hide();
                        uploaded.hide();
                        progress.show();
                        
                        // Send file
                        $.ajax({
                            url: app.settings.urlFileUpload,
                            processData: false,
                            contentType: false,
                            type: 'POST',
                            xhr: function() {
                                var xhr = new XMLHttpRequest();
                                xhr.upload.addEventListener('progress', function(e) {
                                    bar.css('width', Math.ceil(e.loaded / e.total) * 100 + '%');
                                }, false);
                                return xhr;
                            },
                            data: function() {
                                var formData = new FormData(), 
                                    formFile = file.get(0).files[0];
                                formData.append('upload', formFile);
                                return formData;
                            }(),
                            success: function(data) {
                                progress.hide();
                                uploaded.show();
                                changed = true;
                                inputAttachment.val(data['upload']);
                                
                                if (data['preview'])
                                {
                                    saveContext.find('.screenshot img').remove();
                                    saveContext.find('.screenshot').append('<img src="data:image/jpeg;base64,' + data['preview'] + '" />');
                                }
                                
                            }
                        });
                        
                    });
                    
                    // File delete
                    removeLink.click(function() {
                        if (self.is('.img')) saveContext.find('.screenshot img').remove();
                        emptyDeleted.show();
                        emptyDefault.hide();
                        uploaded.hide();
                        progress.hide();
                        changed = true;
                        inputAttachment.val('delete');
                        return false;
                    });
                    
                });
                
                // Form changed state
                saveContext.find('input, select').bind('input propertychange change cut paste', function() {changed = true;});
                
                // Cancel on escape
                $(document).bind('keydown', function(e) {if (e.keyCode === 27) saveContext.find('.button.cancel').click();})
                
                saveContext.show().siblings().hide();
                
            });
            
            // Mark as initialized
            context.data('initialized', true);
        }
        else
            // Show this block
            context.show().siblings().hide();

        return false;
    };
    
    App.prototype.actionOptions = function()
    {
        var context = app.getContext('options', app.settings),
            table = context.find('.table');
        
        // Initialize context
        if (!context.data('initialized'))
        {
            // Attach dataTables
            table = context.find('.table').dataTable({
                bProcessing: true,
                bServerSide: true,
                bPaginate: false,
                sDom: 'rt',
                sAjaxSource: app.settings.urlOptionList,
                aoColumns:
                [
                    {mData: 'id', sWidth: 50, sClass: 'center', bSortable: false},
                    {mData: 'enabled:label', sWidth: 75, sClass: 'center', bSortable: false},
                    {mData: 'name', sClass: 'blue', bSortable: false},
                    {mData: 'price', sWidth: 75, sClass: 'center green', bSortable: false, mRender: function(v) {return (v !== null) ? '$' + v : '-';}},
                    {mData: 'id', sWidth: 25, sClass: 'center', bSortable: false, mRender: function(v) {return '<a class="float-right action delete" href="#delete" title="Удалить опцию">&nbsp;</a>';}}
                ]
            });
            
            // Attach delete action
            table.on("click", "tbody tr td a[href=#delete]", function(e) {
                var link = $(this),
                    row = link.closest('tr'),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                
                jConfirm('Do you want to delete option?', 'Delete option', function(confirm) {
                    if (confirm) {
                        table.fnSettings().sAjaxSource = app.settings.urlOptionList + '?delete=' + data['id'];
                        table.fnSettings().aoDrawCallback.push({"fn": function() {$.alerts._hide();}});
                        table.fnDraw(false);
                        return true; // Do not hide alert - we'll handle this
                    }
                });
                
                return false;
            });
            
            // Attach editing
            table.on("click", "tbody tr", function(e) {
                var row = $(this),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                
                context.find('.button.create').trigger('click', data);
            });
            
            // Attach create
            context.find('.button.create').click(function(e, data) {
                
                if (!data) data = {name: '', price: '0.00', enabled: true};
                
                var popup = app.getContext('manage_option', data, true),
                    modal = popup.modal({onClose: function() {$.modal.close(); popup.remove();}});
                
                // Plugins
                popup.find('input:checkbox, input:radio').uniform({checkboxClass: 'check'});
                popup.find('form').validate({errorElement: 'div', onfocusout: false, onsubmit: false, rules: {
                    name: {required: true},
                    price: {required: true, number: true},
                }});
                
                // Attach form submit
                popup.find('form').submit(function() {
                    if ($(this).validate().form())
                    {
                        table.fnSettings().sAjaxSource = app.settings.urlOptionList + '?insert=1&' + $(this).serialize();
                        table.fnSettings().aoDrawCallback.push({"fn": function() {modal.close();}});
                        table.fnDraw(false);
                    }
                    return false;
                });
                
                // Attach cancel button
                popup.find('.button.cancel').click(function() {
                    modal.close();
                    return false;
                });
                
                return false;
            });
            
            // Mark as initialized
            context.data('initialized', true);
        }
        else
            // Show this block
            context.show().siblings().hide();
        
        return false;
    }
    
    App.prototype.actionDiscounts = function()
    {
        var context = app.getContext('discounts', app.settings),
            table = context.find('.table');
        
        // Initialize context
        if (!context.data('initialized'))
        {
            // Attach dataTables
            table = context.find('.table').dataTable({
                bProcessing: true,
                bServerSide: true,
                bPaginate: false,
                sDom: 'rt',
                sAjaxSource: app.settings.urlDiscountList,
                aoColumns:
                [
                    {mData: 'id', sWidth: 50, sClass: 'center', bSortable: false},
                    {mData: 'enabled:label', sWidth: 75, sClass: 'center', bSortable: false},
                    {mData: 'destination:label', sClass: 'blue', bSortable: false},
                    {mData: 'value', sWidth: 75, sClass: 'center green', bSortable: false, mRender: function(v) {if (v === null) return '-'; if (/\%$/.test(v)) return v; else return '$' + v;}},
                    {mData: 'id', sWidth: 25, sClass: 'center', bSortable: false, mRender: function(v) {if (v == 1) return '&nbsp;'; return '<a class="float-right action delete" href="#delete" title="Удалить опцию">&nbsp;</a>';}}
                ]
            });
            
            // Attach delete action
            table.on("click", "tbody tr td a[href=#delete]", function(e) {
                var link = $(this),
                    row = link.closest('tr'),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                
                jConfirm('Do you want to delete discount?', 'Delet discount', function(confirm) {
                    if (confirm) {
                        table.fnSettings().sAjaxSource = app.settings.urlDiscountList + '?delete=' + data['id'];
                        table.fnSettings().aoDrawCallback.push({"fn": function() {$.alerts._hide();}});
                        table.fnDraw(false);
                        return true; // Do not hide alert - we'll handle this
                    }
                });
                
                return false;
            });
            
            // Attach editing
            table.on("click", "tbody tr", function(e) {
                var row = $(this),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                
                context.find('.button.create').trigger('click', data);
            });
            
            // Attach create
            context.find('.button.create').click(function(e, data) {
                
                if (!data) data = {deletable: true, enabled: true};
                
                var popup = app.getContext('manage_discount', data, true),
                    modal = popup.modal({onClose: function() {$.modal.close(); popup.remove();}});
                
                // Plugins
                popup.find('input#target').autocomplete(app.settings.urlDiscountList, {autoFill: true, maxItemsToShow: 10});
                popup.find('input:checkbox, input:radio').uniform({checkboxClass: 'check'});
                popup.find('form').validate({errorElement: 'div', onfocusout: false, onsubmit: false,
                    rules: {
                    target: {required: true, 'discount-target': true},
                    value: {required: true, 'discount-value': true},
                }});
                
                // Attach form submit
                popup.find('form').submit(function() {
                    if ($(this).validate().form())
                    {
                        table.fnSettings().sAjaxSource = app.settings.urlDiscountList + '?insert=1&' + $(this).serialize();
                        table.fnSettings().aoDrawCallback.push({"fn": function() {modal.close();}});
                        table.fnDraw(false);
                    }
                    return false;
                });
                
                // Attach cancel button
                popup.find('.button.cancel').click(function() {
                    modal.close();
                    return false;
                });
                
                return false;
            });
            
            // Mark as initialized
            context.data('initialized', true);
        }
        else
            // Show this block
            context.show().siblings().hide();
        
        return false;
    };
    
    App.prototype.actionGroups = function()
    {
        var context = app.getContext('groups', app.settings),
            table = context.find('.table');
        
        // Initialize context
        if (!context.data('initialized'))
        {
            // Attach dataTables
            table = context.find('.table').dataTable({
                bProcessing: true,
                bServerSide: true,
                bSorting: false,
                bPaginate: false,
                sDom: 'rt',
                sAjaxSource: app.settings.urlGroupList,
                aoColumns:
                [
                    {mData: 'id', sWidth: 50, sClass: 'center', bSortable: false},
                    // {mData: 'enabled', sWidth: 75, sClass: 'center', bSortable: false, mRender: function(v) {return '<input type="checkbox"' + (v == 1 ? ' checked="checked"' : '') + ' disabled="disabled"/>';}},
                    {mData: 'enabled:label', sWidth: 75, sClass: 'center', bSortable: false},
                    {mData: 'name', sClass: 'blue', bSortable: false},
                    {mData: 'type', sWidth: 75, sClass: 'center', bSortable: false},
                    {mData: 'id', sWidth: 25, sClass: 'center', bSortable: false, mRender: function(v) {return '<a class="float-right action delete" href="#delete" title="Удалить опцию">&nbsp;</a>';}}
                ]
            });
            
            // Attach delete action
            table.on("click", "tbody tr td a[href=#delete]", function(e) {
                var link = $(this),
                    row = link.closest('tr'),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                
                jConfirm('Do you want to delete group?', 'Delete group', function(confirm) {
                    if (confirm) {
                        table.fnSettings().sAjaxSource = app.settings.urlGroupList + '?delete=' + data['id'];
                        table.fnSettings().aoDrawCallback.push({"fn": function() {$.alerts._hide();}});
                        table.fnDraw(false);
                        return true; // Do not hide alert - we'll handle this
                    }
                });
                
                return false;
            });
            
            // Attach editing
            table.on("click", "tbody tr", function(e) {
                var row = $(this),
                    pos = table.fnGetPosition(row[0]),
                    data = table.fnGetData(pos);
                
                context.find('.button.create').trigger('click', data);
            });
            
            // Attach create
            context.find('.button.create').click(function(e, data) {
                
                if (!data) data = {
                    name: '',
                    enabled: true,
                    type: 'STANDARD'
                };
                
                var popup = app.getContext('manage_group', data, true),
                    modal = popup.modal({onClose: function() {$.modal.close(); popup.remove();}});
                
                // Plugins
                popup.find('select').val(data['type']).dropkick();
                popup.find('input:checkbox, input:radio').uniform({checkboxClass: 'check'});
                popup.find('form').validate({errorElement: 'div', sonsubmit: false, rules: {
                    name: {required: true},
                    price: {required: true, number: true},
                }});
                
                // Attach form submit
                popup.find('form').submit(function() {
                    if ($(this).validate().form())
                    {
                        table.fnSettings().sAjaxSource = app.settings.urlGroupList + '?insert=1&' + $(this).serialize();
                        table.fnSettings().aoDrawCallback.push({"fn": function() {modal.close();}});
                        table.fnDraw(false);
                    }
                    return false;
                });
                
                // Attach cancel button
                popup.find('.button.cancel').click(function() {
                    modal.close();
                    return false;
                });
                
                return false;
            });
            
            // Mark as initialized
            context.data('initialized', true);
        }
        else
            // Show this block
            context.show().siblings().hide();
        
        return false;
    };
    
    App.prototype.actionHistory = function()
    {
        var context = app.getContext('history', app.settings),
            table = context.find('.table');
        
        // Initialize context
        if (!context.data('initialized'))
        {
            // Attach dataTables
            table = context.find('.table').dataTable({
                bProcessing: true,
                bServerSide: true,
                sAjaxSource: app.settings.urlHistory,
                aoColumns:
                [
                    {mData: 'message'},
                    {mData: 'name', sClass: 'center'},
                    {mData: 'created', sWidth: 100, sClass: 'center', mRender: function(v) {return moment.unix(v).fromNow();}}
                ]
            });
            
            // Mark as initialized
            context.data('initialized', true);
        }
        else
            // Show this block
            context.show().siblings().hide();
        
        return false;
    };

})(jQuery, window, document);