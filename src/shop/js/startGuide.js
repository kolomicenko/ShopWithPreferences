/**
 * @file
 * Quick user guide showing and hiding (used by catalog.php).
 */

$(document).ready(function(){
    $('#startGuide').click(function(){
        $(this).fadeOut(500, function(){
            $('#showGuideAgain').show();
        });
    });
    $('#showGuideAgain span').click(function(){
        $(this).parent().hide();
        $('#startGuide').fadeIn(500);
    });
});

