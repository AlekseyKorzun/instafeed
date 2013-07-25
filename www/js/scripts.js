$(document).ready(function() {
    $('form').submit(function() {
        var name = $(this).find('input').val();
        window.location.href = '/tag/' + name.replace(/\W/g, '');
        return false;
    });
});