$(document).ready(function() {
    
    // Uniform
    $(".check, .radio").uniform();
    
    // Slider
    $('#question_count').each(function(e) {
        
        var input = $(this),
            label = $('<span class="question-count">&nbsp;</span>').text(input.val() + ' questions').insertAfter(input),
            slider = $('<div />').slider({
                value: input.val(),
                min: 1,
                max: 10,
                step: 1,
                slide: function( event, ui ) {
                   label.text(ui.value + ' questions');
                }
            }).insertAfter(label);
        
        input.hide();
    });
    
    // Discount code
    $('.discount').each(function(e) {
       
        var block = $(this).hide(),
            input = block.find('input[type=text]'),
            submit = block.find('input[type=submit]'),
            loader = $('<div class="discount-load" />').insertAfter(block).hide(),
            link = $('<a href="#" class="discount-link">Got discount?</a>').insertBefore(block),
            error = $('<span>Wrong code. </span><a href="#" class="discount-link">Try again?</a>').insertBefore(block).hide(),
            text = $('<span class="discount-text"></span>').insertAfter(block).hide();
            
            link.click(function(e) {
                block.show();
                link.hide();
                input.focus();
                return false;
            });
            
            error.click(function(e) {
                block.show();
                error.hide();
                input.val('');
                input.focus();
                return false;
            });
            
            input.keydown(function(e) {
                if (e.keyCode == 27)
                {
                    block.hide();
                    link.show();
                }
                if (e.keyCode == 13)
                    submit.trigger('click');
            });
            
            submit.click(function(e) {
                block.hide();
                // link.show();
                
                $.ajax({
                    url: 'check.php', 
                    type: 'POST', 
                    data: {coupon: input.val()},
                    beforeSend: function ( xhr ) {
                        loader.show();
                    }
                })
                .done(function(data) {
                    loader.hide();
                    json = $.parseJSON(data);
                    if (json === false)
                        error.show();
                    else
                    {
                        text.text('$' + json.value);
                        text.show();
                    }
                });
                
            });
        
    });
    
    // Open window
    $('.popup').click(function(e) {
        window.open('../test/index.html', '', 'fullscreen=yes, scrollbars=auto');
        return false;
    });
    
    // Show graph
    $('#tab-1').each(function(e) {
        
        var container = $(this),
            chart = null,
            options = {
                chart: {
                    renderTo: 'tab-1',
                    type: 'column',
                    height: 200,
                    marginTop: 20,
                    animation: false,
                    backgroundColor: 'transparent',
                    plotBackgroundColor: '#FFFFFF'
                },
                //colors: ['#3681C0', '#1BA926'],
                credits: {enabled: false},
                title: {text: ''},
                subtitle: {text: ''},
                xAxis: {tickInterval: 1/* type: 'datetime' */},
                yAxis: [{
                    min: 0,
                    max: 60,
                    allowDecimals: false,
                    endOnTick: true,
                    lineWidth: 1,
                    tickWidth: 1,
                    tickLength: 4,
                    tickInterval: 10,
                    gridLineWidth: 1,
                    gridLineColor: '#EFEFEF',
                    //alternateGridColor: '#FAFAFA',
                    //labels: {style: {color: '#1BA926'}},
                    title: {text: 'Test Score', style: {color: '#3681C0'}}
                },{
                    min: 0,
                    max: 100,
                    tickInterval: 20,
                    allowDecimals: false,
                    lineWidth: 1,
                    tickWidth: 1,
                    tickLength: 4,
                    gridLineWidth: 1,
                    //tickInterval: 10,
                    opposite: true,
                    title: {text: 'Quiz Score', style: {color: '#1BA926'}}
                }],
                legend: {
                    enabled: false,
                    layout: 'vertical',
                    backgroundColor: '#FFFFFF',
                    align: 'right',
                    verticalAlign: 'top',
                    shadow: true
                },
                tooltip: {
                    formatter: function() {
                        return '<b>' + this.point.info.type + '</b><br />' + 
                            'Correct answers: ' + this.point.info.label + '<br />' +
                            'Average response: ' + this.point.info.avgResponse + '<br />' + 
                            'Average difficulty: ' + this.point.info.avgDifficulty + '<br />' + 
                            'Time taken: ' + this.point.info.totalTime;
                    }
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 1,
                        borderRadius: 2,
                        animation: false,
                        cursor: 'pointer',
                        shadow: false,
                        dataLabels: {
                            enabled: true,
                            color: '#666666',
                            //y: 15,
                            //rotation: -45,
                            //padding: 0,
                            //borderRadius: 2,
                            //backgroundColor: '#FFFFFF'
                            style: {fontWeight: 'bold'},
                            formatter: function()
                            {
                                return this.point.info.label;
                            }
                        }
                    },
                    series: {
                        point: {
                            events: {
                                click: function() {
                                    location.href = 'results.html';
                                }
                            }
                        }
                    }
                },
                series: [{
                    type: 'column',
                    name: 'Scores',
                    yAxis: 0
                }]
            };
        
        
        $.getScript('scripts/data.js', function() {
            
            var data = [];
            var categories = [];
            
            $.each(json, function(i, v) {
                categories.push(v.name);
                v.color = v.info.label.match(/^[QV]/) ? '#3681C0' : '#1BA926';
                v.y = v.info.label.match(/^\d+\%/) ? v.info.value * 60 / 100 : v.info.value;
                data.push(v);
            });
            
            options.series[0].data = data;
            options.xAxis.categories = categories;
            
            chart = new Highcharts.Chart(options);
            
        });
        
    });
    
    // Tabs
    $('.tab').each(function() {
       
       var tab = $(this),
           href = tab.attr('href');
       
       tab.click(function(e) {
        
          if (tab.hasClass('sel')) return;
          
          tab.parent().find('.sel').removeClass('sel');
          tab.addClass('sel');
          
          $(href).parent().children().hide();
          $(href).show();
           
       });
        
    });
    
});