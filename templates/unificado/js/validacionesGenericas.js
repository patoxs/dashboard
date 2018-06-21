
    function numeroMinMax(campo,min,max)
    {
        var valida = new Validador();
        valida.eliminaErrores();
        var valor = $("#"+campo).val().trim();

        if (valor<min||valor>max||!valor) {
            $("#"+campo).val('');
            valida.imprimeErroDeFormato(campo, 'numero');
            return false;
        }
    }

    function texto(campo)
    {
        var valida = new Validador();
        valida.eliminaErrores();
        
        var valor = $("#"+campo).val().trim();  

        var validado = valida.compruebaTipo(valor, 'texto');
        if (!validado||!valor) {
            $("#"+campo).val('');
            valida.imprimeErroDeFormato(campo, 'texto');
            return false;
        }
    }

    function email(campo)
    {
        var valida = new Validador();
        valida.eliminaErrores();
        var valor = $("#"+campo).val().trim();
        
        var validado = valida.compruebaTipo(valor, 'email');
        if (!validado||!valor) {
            $("#"+campo).val('');
            valida.imprimeErroDeFormato(campo, 'email');
            return false;
        }
    }

    function correo(campo)
    {
        var valida = new Validador();
        valida.eliminaErrores();
        var valor = $("#"+campo).val().trim();
        
        var validado = valida.compruebaTipo(valor, 'email');
        if (!validado||!valor) {
            $("#"+campo).val('');
            valida.imprimeErroDeFormato(campo, 'email');
            return false;
        }
    }

    function numero(campo)
    {
        var valida = new Validador();
        valida.eliminaErrores();
        var valor = $("#"+campo).val().trim();
        var validado = valida.compruebaTipo(valor, 'numero');
        if (!validado||!valor) {
            $("#"+campo).val('');
            valida.imprimeErroDeFormato(campo, 'numero');
            return false;
        }
        
        $("#"+campo).val(parseInt(valor));
    }

    function valida_rut(campo)
    {
        var valor = $("#"+campo).val();
        var valida = new Validador();
        valida.eliminaErrores();
        var validado = valida.compruebaTipo(valor, 'rut');
        if (!validado||!valor) {
            $("#"+campo).val('');
            valida.imprimeErroDeFormato(campo, 'rut');
            return false;
        }
        return true;  
    }

    function formato_rut(campo)
    {
        var sRut1 = $("#"+campo).val();
        sRut1 = sRut1.replace(/[\.]/g,'');
        sRut1 = sRut1.replace(/[\-]/g,'');
        sRut1 = sRut1.replace(/[kK]/,'K');
        var nPos = 0;
        var sInvertido = "";
        var sRut = "";
        
        for(var i = sRut1.length - 1; i >= 0; i-- )
        {
            sInvertido += sRut1.charAt(i);
            if (i == sRut1.length - 1 )
                sInvertido += "-";
            else if (nPos == 3)
            {
                sInvertido += ".";
                nPos = 0;
            }
            nPos++;
        }
        
        for(var j = sInvertido.length - 1; j >= 0; j-- )
        {
            if (sInvertido.charAt(sInvertido.length - 1) != ".")
                sRut += sInvertido.charAt(j);
            else if (j != sInvertido.length - 1 )
                sRut += sInvertido.charAt(j);
        }
        return sRut.toUpperCase();
    }

    function fecha(campo)
    {
        var valor = $("#"+campo).val();
        var valida = new Validador();
        valida.eliminaErrores();
        var validado = valida.compruebaTipo(valor, 'fecha');
        if (!validado||!valor) {
            $("#"+campo).val('');
            valida.imprimeErroDeFormato(campo, 'fecha');
            return false;
        }
    }

    function esJsonValido(str)
    {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }


    function existeFuncionSinParametros(str)
    {
        var fn = window[str];
        if(typeof fn === 'function'){
            return true
        }else{
            return false
        }
    }
    