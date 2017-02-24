$(document).ready(function() {
    
    // Fix content height
    $(window).resize(function(e) {$(".content").height(($(document).height() - 80 - 40) + 'px');})
    $(window).trigger('resize');
    
    // Bookmarking the question
    $(".bookmark").click(function(e) {
        
        var link = $(this),
            icon = link.find('.icon');
        
        if (icon.is('.i-star-on'))
        {
            icon.removeClass('i-star-on').addClass('i-star-off');
            // Add callback to remove question bookmark.
        }
        else
        {
            icon.removeClass('i-star-off').addClass('i-star-on');
            // Add callback to add question bookmark.
        }
        
    });
    
    // Toggling controls.
    $(".control").click(function(e) {
        
        var link = $(this),
            span = link.find('span');
        
        span.toggle();
        
    });
    
    // Countdown timer.
    $("#countdown").each(function(e) {
        
        var span = $(this),
            total = parseInt(span.text()),
            minutes = Math.floor(total / 60),
            seconds = total - 60 * minutes;
            
        var update = function() {
            
            span.text(((minutes < 10) ? '0' + minutes : minutes) + ':' + ((seconds < 10) ? '0' + seconds : seconds));
            
            if (seconds-- == 0)
            {
                seconds = 59;
                if (minutes-- == 0)
                {
                    jAlert("Click OK to continue.", "Time Expired");
                    top.location.reload();
                }
                    
            }
        }
        
        update();
        setInterval(update, 1000);
        
    });
    
    // Disable clear/show links.
    if (!$(".option input:checked").val())
        $(".guessed a").css('color', '#BABABA');
    
    // Enable chear/show links.
    $(".option input[type=radio]").change(function() {
       $(".guessed a").css('color', '');
    });
    
    // Uniform
    $(".option input").uniform();
    
    // Alerts
    $.alerts.dialogClass = 'popup';
    $.alerts.okButton = '<u>Y</u>es';
    $.alerts.cancelButton = '<u>N</u>o';
    
    // Document shortcuts
    $(document).keyup(function(e) {
        
        var key = String.fromCharCode(e.keyCode).toUpperCase(),
            alt = e.altKey || e.metaKey;
        
        //alert(e.keyCode)
        
        if (!alt)
            $(document.body).removeClass('alt');
        
    });
    
    $(document).keydown(function(e) {
        
        var key = String.fromCharCode(e.keyCode).toUpperCase(),
            alt = e.altKey || e.metaKey;
        
        if (alt) $(document.body).addClass('alt');
        
        //alert((alt ? 'ALT + ': '') + key);
        
        // Alerts
        if ($("#popup_container").length > 0)
        {
            if (e.keyCode == 13) $('#popup_ok').trigger('click');
            if (e.keyCode == 27) $('#popup_cancel').trigger('click');
            if (alt && key == 'Y') $('#popup_ok').trigger('click');
            if (alt && key == 'N') $('#popup_cancel').trigger('click');
        }
        
        // Buttons
        else
        {
            if (alt && key == 'F') $('#button_review').trigger('click');
            if (alt && key == 'H') $('#button_help').trigger('click');
            if (alt && key == 'E') $('#button_exit').trigger('click');
            if (alt && key == 'S') $('#button_study').trigger('click');
            if (alt && key == 'N') $('#button_next').trigger('click');
        }
        
    });
    
    
    // Study mode button
    $('#button_study').click(function(e) {
        
        jConfirm('Switch to study mode?');
        
    });
    
    // End exam button
    $('#button_exit').click(function(e) {
        
        if (jConfirm('You are about to end you exam.<br />If you click the Yes button below, your exam will end.<br /><br />Are you sure you want to end your exam?', 'End Exam'))
            top.location.reload();
    
    });

    // Next button
    $('#button_next').click(function(e) {
        
        if (!$(".option input[type=radio]:checked").val())
            jAlert('You cannot continue with this question unanswered.', 'Answer Required');
        
        else if (jConfirm('Click Yes to confirm your answer and continue to the next question.', 'Answer Confirmation'))
            top.location.reload();
    
    });
    
    // Clear answer
    $("#clear_answer").click(function() {
        $(".option input:radio:checked").prop("checked", false).parent().removeClass('checked');
        $(".guessed a").css('color', '#BABABA');
        return false;
    });
    
    // Show answer
    $("#show_answer").click(function() {
        
        if (!$(".option input[type=radio]:checked").val())
            return;
        
        var validAnswer = 'o2',
            checkAnswer = $(".option input[type=radio]:checked").prop("id");
        
        $(".guessed a#clear_answer").hide();
        $(".guessed a#show_answer").hide();
        $(".guessed a#hide_answer").show();
        
        $(".explain").css('height', $(".content").outerHeight() + 'px').toggle();
        
        $(".option .radio#uniform-" + checkAnswer).addClass('invalid');
        $(".option .radio#uniform-" + validAnswer).removeClass('invalid').addClass('valid');
        
        $(".option .radio input").unbind('click').click(function() {return false;});
        
        return false;
    });
    
    // Hide answer
    $("#hide_answer").hide().click(function() {
        
        $(".explain").hide();
        
        //$(".guessed a#clear_answer").show();
        $(".guessed a#show_answer").show();
        $(".guessed a#hide_answer").hide();
        
    });
    
    
    
    // Apply MathJax.
    MathJax.Hub.Config({
        tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
        //"HTML-CSS": {webFont: null, imageFont: "TeX"}
    });
    
    //setTimeout(function() {$('.MathJax').css('font-size', '14px');}, 500);
    //jConfirm(123, 'Answer Confirmation');
    
});