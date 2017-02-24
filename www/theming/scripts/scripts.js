$(document).ready(function()
{
    $(".photo").colorbox({transition: "none", innerWidth: "760px", innerHeight: "1140px", href: "theming/images/photo_big.jpg"});
    
    $('.js-toggle').each(function()
    {
        var self = $(this), id = self.attr('href'), p = $(id);
        p.hide(); self.toggleClass('closed');
        self.click(function()
        {
            self.toggleClass('opened');
            self.toggleClass('closed');
            p.toggle();
            return false;
        });
    });
});