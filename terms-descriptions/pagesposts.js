(function($) {
/**
 * Этот скрипт содержит JS код, который используется для перечлючения
 * списков с перечнем постов и страниц.
 */
update_postspages( $('#termtarget').val() );

//назначаем обработчик для события change
$('#termtarget').change( function() {
	update_postspages( $(this).val() );
});

//эта функция изменяет содержимое списка с перечнем постов/страниц
function update_postspages( type ) {
	var target = $( '#termpageid' );
    //в этих переменных - элементы input и select для выбора страниц и ввода внешнего URL
    var input_field = $( '<input type="text" name="termpageid" id="termpageid" value="' + external_page + '" />' );
    var select_field = $( '<select name="termpageid" id="termpageid"><option>---</option></select>' );
	//переменные posts и pages создаются php скриптом (termdescription.php) 
	//и содержат теги option
	if ( 'posts' == type ) {
        target.after( select_field ).remove();
        target = $( '#termpageid' );
		target.html( posts );
	}
	else if ( 'pages' == type ) {
        target.after( select_field ).remove();
        target = $( '#termpageid' );
		target.html( pages );
	}
    else if ( 'external' == type ) {
        target.after( input_field ).remove();
    }
}

})(jQuery);
