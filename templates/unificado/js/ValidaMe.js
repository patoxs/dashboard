/*!
 * ValidaMe
 *
 * ValidaMe it's a simple js form validation who integrates
 * The most popular CSS Frameworks.
 *
 * Actually Integrates:
 * 
 * - Foundation 5
 * --------------------------------------------------------
 * 
 * @author Mauricio Beltran <mauricio.beltran@usolix.cl>
 * @version 1.0.0
 * @license GPL v2.0
 * --------------------------------------------------------
 *
 * Version modificada de ValidaMe.js para INDAP
 * @source https://github.com/metalback/ValidaMe/blob/master/ValidaMe.js
 *
 */

var ValidaMe = {
    /**
     * Library configuration
     * @type {Object}
     */
    configuration : {
        mark:'data-type',
        frame:'Foundation5'
    },

    init: function(custom_config)
    {
        for(var i in custom_config) {
            ValidaMe.configuration[i] = custom_config[i];
        }

        /**
         * Group of verified radio
         * @type {Array}
         */
        ValidaMe.radio_verified = new Array();

        /**
         * Group of verified checkbox
         * @type {Array}
         */
        ValidaMe.checkbox_verified = new Array();

        /**
         * Response of tthe actual validation
         * @type {Boolean}
         */
        ValidaMe.state = true;
    },    

    /**
     * Supported RegEx
     * @type {Object}
     */
    expressions : {
        numero: /^[\-]{0,1}\d+$/,
        decimal: /^[\-]{0,1}\d+\.{0,1}\d*$/, 
        minusculas: /^[a-z]+$/, 
        texto: '', 
        solo_texto: /^[a-zA-Z\ \.\,\;\'ñÑáéíóúÁÉÍÓÚüÜ]+$/,
        direccion: /^[a-zA-Z0-9\ \,\#\'\°ñÑáéíóúÁÉÍÓÚüÜ']+$/,
        nombre: /^[a-zA-Z\ \'ñÑáéíóúÁÉÍÓÚüÜ]+$/, 
        password: /.*^(?=.{4,10})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$/, 
        fecha: /^[0-9]{2}[\-\/]{1}[0-9]{2}[\-\/]{1}[0-9]{4}$/,
        email: /^[a-z]*[a-zA-Z0-9\_\-\.]+\@([a-z0-9]+(\.)*)+(\.[a-z]{1,3})+$/,
        moneda: /^\$[\ \d\.]+(\.\-)?$/   
    },

    /**
     * Supported default errors
     * @type {Object}
     */
    common_errors : {
        numero: 'Ingrese un número',
        decimal: 'Ingrese un número decimal valido',
        minusculas: 'Debe ingresar solo minúsculas',
        texto: '',
        solo_texto: 'Debe ingresar caracteres alfabeticos validos',
        direccion: 'Debe ingresar una direccion válida',
        nombre: 'Debe ingresar una cadena válida para nombre',  
        password: 'Su contraseña no cumple los requerimientos minimos (4 minimo, 10 maximo, al menos un caracter minusucla, mayuscula, un numero y un caracter especial)', 
        fecha: 'Formato de fecha inválido (dd-mm-aaaa or dd/mm/aaaa)',     
        email: 'Debe ingresar un correo válido',        
        empty: 'Este campo no puede estar vacío',
        select: 'Debe seleccionar una opción',
        checkbox: 'Debe chequear al menos una opción',
        radio: 'Debe seleccionar una opción',
        meses: 'Debe ingresar al menos un mes con actividades',
        rut: 'Debe ingresar un RUT válido',
        moneda: 'Debe ingresar un monto válido'
    },
    

    /**
     * Main method
     * @return {Boolean} true if pass, false otherwise
     */
    validate : function()
    {     
        ValidaMe.init();
        ValidaMe.cleanError();         
        $(document).find('['+ValidaMe.configuration.mark+']').each(function() {
            ValidaMe.checkElement($(this));
        });
        if (ValidaMe.state==false) {
            ValidaMe.focusError();
        };
        return ValidaMe.state;
    },

    /**
     * Check the element
     * @param  {Object} element Html element
     * @return {Void}
     */
    checkElement : function(element)
    {
        var element_implement = element[0].localName;
        var element_type = element[0].type;
        if (element_implement=='select') {
            ValidaMe.verifySelect(element);
        } else if(element_type=='radio') {
            ValidaMe.verifyRadio(element);
        } else if(element_type=='checkbox') {
            ValidaMe.verifyCheckbox(element);
        } else if(element_type=='textarea') {
            ValidaMe.verifyInput(element);
        } else if (element_implement=='input') {
            ValidaMe.verifyInput(element);
        };        
    },

    /**
     * Begin validation process of a checkbox, setting state
     * @param  {Object} element Html element
     * @return {Void}
     */
    verifyCheckbox : function(element)
    {
        var type = element.attr(ValidaMe.configuration.mark);
        var checkbox_name = element[0].name;
        if (ValidaMe.checkbox_verified.indexOf(checkbox_name)==-1) {
            ValidaMe.checkbox_verified.push(checkbox_name);
            var checkbox_checked = false;
            $('[name="'+checkbox_name+'"]').each(function(){
                if ($(this).is(":checked")) {
                    checkbox_checked = true;
                };
            });

            if (!checkbox_checked) {
                ValidaMe.printError(element, type);
                ValidaMe.state = false;
            };
        };        
    },

    /**
     * Begin validation process of a radio, setting state
     * @param  {Object} element Html element
     * @return {Void}
     */
    verifyRadio : function(element)
    {
        var type = element.attr(ValidaMe.configuration.mark);
        var radio_name = element[0].name;
        if (ValidaMe.radio_verified.indexOf(radio_name)==-1) {
            ValidaMe.radio_verified.push(radio_name);
            var radio_checked = false;
            $('[name="'+radio_name+'"]').each(function(){
                if ($(this).is(":checked")) {
                    radio_checked = true;
                };
            });

            if (!radio_checked) {
                ValidaMe.printError(element, type);
                ValidaMe.state = false;
            };
        };        
    },

    /**
     * Verify the select state
     * @param  {Object} element Html object
     * @return {Void}
     */
    verifySelect : function(element)
    {
        var type = element.attr(ValidaMe.configuration.mark);
        if (element.val()==0) {
            ValidaMe.printError(element, type);
            ValidaMe.state = false;
        };
    },

    /**
     * Verify a standar input
     * @param  {Object} element Html element
     * @return {Void}
     */
    verifyInput : function(element)
    {
        var content = element.val().trim();
        var type = element.attr(ValidaMe.configuration.mark);
        try {
            ValidaMe.verifyEmpty(content, element);
            ValidaMe.verifyType(element);
        } catch (err) {
            ValidaMe.state = err;
        }

    },

    /**
     * Verify if the element is empty
     * @param  {String} content content of the input
     * @param  {Object} element Html element
     * @return {Void}
     */
    verifyEmpty : function(content, element)
    {
        if (content.length==0) {
            ValidaMe.printError(element, 'empty');            
            throw false;
        };
    },

    /**
     * Verify the type value of an input
     * @param  {Object} element Html element
     * @return {Void}
     */
    verifyType : function(element) 
    {
        var content = element.val().trim();
        var type = element.attr(ValidaMe.configuration.mark);
        if (!content.match(ValidaMe.expressions[type])) {
            ValidaMe.printError(element, type);
            throw false;
        };
    },

    /**
     * Main method of print error
     * @param  {Object} element    Html element
     * @param  {String} type_error Type of error fired
     * @return {Void}
     */
    printError : function(element, type_error)
    {
        eval('ValidaMe.printError'+ValidaMe.configuration.frame+'(element, type_error)');
    },

    /**
     * Clean justified errors
     * @return {Void}
     */
    cleanError : function()
    {
        eval('ValidaMe.cleanError'+ValidaMe.configuration.frame+'()');
    },

    /**
     * Focus on the first error fired
     * @return {Void}
     */
    focusError : function(){
       eval('ValidaMe.focusError'+ValidaMe.configuration.frame+'()');
    },

    printErrorFoundation5 : function(element, type_error)
    {
        var error_message = ValidaMe.common_errors[type_error];
        if (element[0].type=='radio'||element[0].type=='checkbox') { // Verificar aca
            $(element).first().parent().append("<small class='error'>Error: "+error_message+"</small>");
        } else {
            $(element).parent().addClass("error");
            $(element).parent().parent().append("<small class='error'>Error: "+error_message+"</small>");
        }        
    },

    cleanErrorFoundation5 : function()
    {
        $("label").removeClass("error");
        $("small").remove();
    },

    focusErrorFoundation5 : function()
    {
        $('html, body').animate({
            scrollTop: ($('.error').first().offset().top)
        },500);
        return false;
    }
}