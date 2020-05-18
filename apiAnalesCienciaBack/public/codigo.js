    function slideToggleAutores() {
        $('#persons').slideToggle();
        $('#entities').slideUp();
        $('#products').slideUp();
    }

    function slideToggleEntidades() {
        $('#entities').slideToggle();
        $('#persons').slideUp();
        $('#products').slideUp();
    }

    function slideToggleProductos() {
        $('#products').slideToggle();
        $('#persons').slideUp();
        $('#entities').slideUp();
    }

    function login(){
        let opacidad = 0;

        if(opacidad === 0) {
            $(".loginElement").removeAttr("disabled");
            $("#login").animate({
                opacity: '1'
            });
            opacidad = 1;
        }
        else {
            $('.loginElement').attr('disabled', 'disabled');
            // TODO deshabilitar el boton para clicks muy seguidos
            {$("#login").animate({
                opacity: '0'
            });}
            opacidad=0;
        }
        $('#entities').slideUp();
        $('#persons').slideUp();
        $('#products').slideUp();
    }

    async function cerrar() {

        /*$('.banda').animate({
            opacity: '0'
        });*/

        await new Promise(r => setTimeout(r, 200));
        $("#ficha").animate({
            opacity: '0',
            height: '0px',
            width: '0px',
        });

        await new Promise(r => setTimeout(r, 300));
        $('.logoCentral').removeClass("escondida");
        $('.banda').removeClass("escondida");
        $('.banda').animate({
            opacity: '0.87'
        });
    }

    //TODO Terminar la funcion abrir
    async function abrir(){

        $('.banda').animate({
            opacity: '0'
        });

        $('.logoCentral').addClass("escondida");
        $('.banda').addClass("escondida");

        await new Promise(r => setTimeout(r, 300));

        $("#ficha").show();
        $("#ficha").animate({
            opacity: '1',
            height: '550px',
            width: '550px',
        });

    }

    function request() {
        $('form').bind('submit', function () {

            $.ajax({
                type: 'POST',
                url: '/access_token',
                data: $('form').serialize(),
                success: function (data, textStatus, request) {
                    //alert('form was submitted');
                    authHeader = request.getResponseHeader('Authorization');
                    let usuarioActual = document.getElementById('username').value;
                    retrieveProducts();
                    retrievePersons();
                    retrieveEntities();
                    retrieveUserType(authHeader, usuarioActual);
                }, error: function (){
                        window.alert("Usuario y/o contraseña no validos");
                        location.reload();
                    },
            });
            return false;
        });
    }

    function retrieveUserType(authHeader, usuarioActual) {

        $.ajax({
            type: 'GET',
            url: 'api/v1/users',
            headers: {"Authorization": authHeader},
            dataType: 'json',
            success: function (data) {

               /* console.log(data['users']);
                console.log(data['users'][1]);
                console.log(data['users'][1]['user']);
                console.log(data['users'][1]['user'].username);*/
                data['users'].forEach(i =>
                {
                    /*console.log(i);
                    console.log(i['user'].username);
                    console.log(i['user'].role);*/
                    if (usuarioActual === i['user'].username) {
                        const tipo = i['user'].role;
                        localStorage.usuarioRegistrado = tipo;
                        imIn();
                        return false;
                    }
                })
            },
        })
    }

    function imIn() {
            try {
                let usuarioRegistrado = localStorage.getItem('usuarioRegistrado');

                if (usuarioRegistrado === "reader" || usuarioRegistrado === "writer") {
                    let bodyElement = "";

                    $("#ficha").click(function () {
                        document.getElementById("logout").style.visibility = "visible";
                    });

                    bodyElement = document.getElementById("botonera");
                    bodyElement.innerHTML = "<button class='btn btn-danger' id='logout'>Logout</button>";

                    if (usuarioRegistrado === "writer") {
                        bodyElement.innerHTML += '<a class="btn btn-primary" rel="pop-up" id="crear" onclick="newElement()">Crear</a>';
                    }
                    $("#logout").click(function () {
                        localStorage.removeItem('usuarioRegistrado');
                        location.reload();
                    });
                }
            } catch (error) {
                console.log(error)
            }
        }


    function retrievePersons() {
        let personasElement = document.getElementById("persons");
        //TODO meter esto en el localStorage y comprobar si existe antes de llamar al servidor en cada login
        $.ajax({
            type: 'GET',
            url: 'api/v1/persons',
            headers: {"Authorization": authHeader},
            dataType: 'json',
            success: function (data) {
                data['persons'].forEach(i => {
                    personasElement.innerHTML +=
                        '<span class="dropdown-item" id="' + i['person'].name + '">' +
                        '<img class="dropdownImg" src=' + i['person'].imageUrl+ ' />' +
                        '<span class="name" id="' + i['person'].name + '">' + i['person'].name +
                        '</span>' +
                        '</span>'
                    let enviar = JSON.stringify(data);
                    //crea elementos tipo paco:{json}, juan:{json}, etc...
                    window.localStorage.setItem(i['person'].name, enviar);
                });
            },
        });
    }

    function retrieveEntities() {
        let entidadesElement = document.getElementById("entities");

        $.ajax({
            type: 'GET',
            url: 'api/v1/entities',
            headers: {"Authorization": authHeader},
            dataType: 'json',
            success: function (data) {
                data['entities'].forEach(i => {
                    entidadesElement.innerHTML +=
                        '<span class="dropdown-item" id="' + i['entity'].name + '">' +
                        '<img class="dropdownImg" src=' + i['entity'].imageUrl+ ' />' +
                        '<span class="name" id="' + i['entity'].name + '">' + i['entity'].name +
                        '</span>' +
                        '</span>'
                    let enviar = JSON.stringify(data);
                    //crea elementos tipo paco:{json}, juan:{json}, etc...
                    window.localStorage.setItem(i['entity'].name, enviar);
                });
            },
        });

    }

    function retrieveProducts() {
        let productosElement = document.getElementById("products");

        $.ajax({
            type: 'GET',
            url: 'api/v1/products',
            headers: {"Authorization": authHeader},
            dataType: 'json',
            success: function (data) {
                //console.log(data);
                data['products'].forEach(i => {
                    productosElement.innerHTML +=
                        '<span class="dropdown-item" id="' + i['product'].name + '">' +
                        '<img class="dropdownImg" src=' + i['product'].imageUrl+ ' />' +
                        '<span class="name" id="' + i['product'].name + '">' + i['product'].name +
                        '</span>' +
                        '</span>'
                    let enviar = JSON.stringify(data);
                    //crea elementos tipo paco:{json}, juan:{json}, etc...
                    window.localStorage.setItem(i['product'].name, enviar);
                });
                /*console.log(data['products']);
                console.log(data['products'][1]);
                console.log(data['products'][1]['product']);
                console.log(data['products'][1]['product'].name);*/
                //$('#info').html(JSON.stringify(data));


            },
        });

    }

    async function localizarElemento(elemento, tipo, name) {
        //console.log(elemento);
        //let existe = false;

        let dibujar = "";
        let i = 0;

        for (let found = false; i <= localStorage.length - 1 && !found; i++) {
            //let miNombre = "";
            //let recibir = "";

            dibujar = JSON.parse(elemento);
            console.log(dibujar);
            console.log(tipo);
            switch (tipo) {
                case 'persons': {
                    console.log(dibujar['persons'][i]['person'].name);
                    if (dibujar['persons'][i]['person'].name === name) {
                        found = true;
                        dibujar = dibujar['persons'][i]['person'];
                    }
                    break;
                }
                case 'entities': {
                    console.log(dibujar['entities'][i]['entity'].name);
                    if (dibujar['entities'][i]['entity'].name === name) {
                        found = true;
                        dibujar = dibujar['entities'][i]['entity'];
                    }
                    break;
                }
                case 'products': {
                    console.log(dibujar['products'][i]['product'].name);
                    if (dibujar['products'][i]['product'].name === name) {
                        found = true;
                        dibujar = dibujar['products'][i]['product'];
                    }
                    break;
                }
            }
        }
        await cerrar();
        await pintarFicha(name, tipo, dibujar);
    }


    async function pintarFicha(name, tipo, dibujar) {

        if($('#ficha').hasClass("form-style")){
            $('#ficha').removeClass("form-style");
            $('#ficha').addClass("ficha");
        }

        let usuarioRegistrado = localStorage.getItem('usuarioRegistrado');
        let mostrado = false;
        console.log(name);
        console.log(dibujar);

        $('#persons').slideUp();
        $('#entities').slideUp();
        $('#products').slideUp();
        let dibujarTemp = dibujar;
        //if (dibujar.deathDate === null) dibujarTemp.deathDate = "-";
        console.log(dibujarTemp);

      /*  if (mostrado) {
            mostrado = false;

            $("#ficha").animate({
                opacity: '0',
                height: '0px',
                width: '0px',
            });
            $('.banda').animate({
                opacity: '0.87'
            });

            await new Promise(r => setTimeout(r, 1000));
            $("#ficha").hide();
            document.getElementById("ficha").innerHTML = "";

            $("#ficha").show();
            $("#ficha").animate({
                opacity: '1',
                height: '550px',
                width: '550px',
            });
        }*/

        $('.banda').animate({
            opacity: '0'
        });

        $('.logoCentral').addClass("escondida");
        $('.banda').addClass("escondida");

        document.getElementById("ficha").innerHTML = "";
        // ESTE ES EL JODIDO
        await new Promise(r => setTimeout(r, 300));
        $("#ficha").show();
        $("#ficha").animate({
            opacity: '1',
            height: '550px',
            width: '550px',
        });


        // if (localStorage.getItem(elemento) === mostrar && !mostrado) {

        let template = [
            {
                '<>': 'img', 'src': '${imageUrl}', 'class': 'laImg'
            },
            {
                '<>': 'h1', 'html': '${name}'
            },
            {
                '<>': 'p', 'html': 'Fecha de nacimiento: ${birthDate}'
            },
            {
                '<>': 'p', 'html': 'Fecha de defunción: ${deathDate}'
            },
            {
                '<>': 'a', 'href': '${wikiUrl}', 'html': 'wikipedia', 'class': 'linkWiki'
            }
        ];
        let bodyElement = document.getElementById("ficha");
        bodyElement.innerHTML += '<button class="btn btn-danger" rel="pop-up" id="cerrar" onclick="cerrar();">X</button>';
        let data = [dibujarTemp];
        document.getElementById("ficha").innerHTML += (json2html.transform(data, template));


        if (usuarioRegistrado === "writer") {


            bodyElement.innerHTML += '<div>';
            //TODO hacer funcionar el boton "editar"
            bodyElement.innerHTML += '<button class="btn btn-info transition" rel="pop-up" id="editar">Editar</button>';
            bodyElement.innerHTML += '<button class="btn btn-warning" rel="pop-up" id="eliminar">Eliminar</button>';
            bodyElement.innerHTML += '</div>';
        }

        $('#eliminar').click(function () {
            eliminar(dibujar, tipo);
        });
        $('#editar').click(function () {
            /*console.log(dibujar);
            console.log(dibujar.id);
            console.log(tipo);*/
            //TODO AQUI ANIMA FORMULARIO





            createForm(dibujar, dibujar.id, tipo);
        });
    }

    function deleteFromDB(id, category){
        jQuery.ajax({
            url: 'http://127.0.0.1:8000/api/v1/' + category + '/' + id,
            headers: {"Authorization": authHeader},
            type: 'DELETE',
            success: function(data) {
                //show_items();
            }
        });
    }

    async function eliminar(associatedJson, tipo) {

        console.log(localStorage);
        localStorage.removeItem(associatedJson.name);
        document.getElementById(associatedJson.name).innerHTML='';

        deleteFromDB(associatedJson.id, tipo);

        existe = false;
        await cerrar();
    }

    function newElement() {
        pintarFicha('', '', '').then(r => createForm('', -1, "persons"));

    }


    function createForm(associatedJson, id, tipo) {
        console.log(associatedJson);
       // if(associatedJson.deathDate==='-'){associatedJson.deathDate = null}
        let bodyElement = document.getElementById("ficha");

        bodyElement.innerHTML = '';
        bodyElement.innerHTML += '<button class="btn btn-danger" rel="pop-up" id="cerrar" onclick="cerrar();">X</button>';
        $('#ficha').addClass("form-style");
        $('#ficha').removeClass("ficha");

        bodyElement.innerHTML += '<form id="form" action="">';
        bodyElement = document.getElementById("form");
        bodyElement.innerHTML += '<div class="encabezado" id="encabezadoRadio">';
            bodyElement = document.getElementById("encabezadoRadio");
            bodyElement.innerHTML += '<label class="formTag">Tipo</label>';
            bodyElement.innerHTML += '<hr class="bajoLinea"/>';
        bodyElement.innerHTML += '</div>';

        bodyElement = document.getElementById("form");
            bodyElement.innerHTML += '<fieldset id="radioBotones">';
            bodyElement = document.getElementById("radioBotones");

                bodyElement.innerHTML += '<div class="radioElement" id="radioPersona">';
                    bodyElement = document.getElementById("radioPersona");
                    if(tipo==="persons"){
                        bodyElement.innerHTML += '<input type="radio" id="persona" name="tipo" value="persona" checked>';
                    }
                    else {
                        bodyElement.innerHTML += '<input type="radio" id="persona" name="tipo" value="persona">';
                    }
                    bodyElement.innerHTML += '<label for="persona" class="radioLabel">Persona</label><br>';
                bodyElement.innerHTML += '</div>';
                bodyElement = document.getElementById("radioBotones");

                bodyElement.innerHTML += '<div class="radioElement" id="radioEntidad">';
                    bodyElement = document.getElementById("radioEntidad");
                    if(tipo==="entities") {
                        bodyElement.innerHTML += '<input type="radio" id="entidad" name="tipo" value="entidad" checked>';
                    }
                    else {
                        bodyElement.innerHTML += '<input type="radio" id="entidad" name="tipo" value="entidad">';
                    }
                    bodyElement.innerHTML += '<label for="entidad" class="radioLabel">Entidad</label><br>';
                bodyElement.innerHTML += '</div>';
                bodyElement = document.getElementById("radioBotones");

                bodyElement.innerHTML += '<div class="radioElement" id="radioProducto">';
                    bodyElement = document.getElementById("radioProducto");
                    if(tipo==="products") {
                        bodyElement.innerHTML += '<input type="radio" id="producto" name="tipo" value="producto" checked>';
                    }
                    else {
                        bodyElement.innerHTML += '<input type="radio" id="producto" name="tipo" value="producto">';
                    }
                    bodyElement.innerHTML += '<label for="producto " class="radioLabel">Producto</label>';
                bodyElement.innerHTML += '</div>';
            bodyElement.innerHTML += '</fieldset>';
            bodyElement = document.getElementById("form");


            bodyElement.innerHTML += '<div class="encabezado" id="encabezadoCampos">';
            bodyElement = document.getElementById("encabezadoCampos");
                bodyElement.innerHTML += '<label class="formTag">Campos</label>';
                bodyElement.innerHTML += '<hr class="bajoLinea"/>';
            bodyElement.innerHTML += '</div>';
            bodyElement = document.getElementById("form");

            bodyElement.innerHTML += '<fieldset id="campos">';
            bodyElement = document.getElementById("campos");

                bodyElement.innerHTML += '<div class="fieldDates" id="fieldDates">';
                bodyElement = document.getElementById("fieldDates");
                    bodyElement.innerHTML += '<div class="fieldElement" id="fieldBirthDate">';
                    bodyElement = document.getElementById("fieldBirthDate");
                        bodyElement.innerHTML += '<label for="birthDate">Fecha de nacimiento</label>';
                        if(associatedJson===''){
                            bodyElement.innerHTML += '<input type="date" name="birthDate" id="birthDate" >';
                        }
                        else {
                            bodyElement.innerHTML += '<input type="date" name="birthDate" id="birthDate" value=' + associatedJson.birthDate + '>';
                        }
                    bodyElement = document.getElementById("fieldDates");
                    bodyElement.innerHTML += '</div>';

                    bodyElement.innerHTML += '<div class="fieldElement" id="fieldDeathDate">';
                    bodyElement = document.getElementById("fieldDeathDate");
                        bodyElement.innerHTML += '<label for="deathDate">Fecha de defuncion</label>';
                        if(associatedJson===''){
                            bodyElement.innerHTML += '<input type="date" name="deathDate" id="deathDate" >';
                        } else {
                            bodyElement.innerHTML += '<input type="date" name="deathDate" id="deathDate" value=' + associatedJson.deathDate + '>';
                        }
                        bodyElement = document.getElementById("fieldDates");
                        bodyElement.innerHTML += '</div>';
                        bodyElement = document.getElementById("campos");
                        bodyElement.innerHTML += '</div>';

                bodyElement.innerHTML += '<div class="fieldElement" id="fieldName">';
                bodyElement = document.getElementById("fieldName");
                    bodyElement.innerHTML += '<label for="name" class="fieldTag">Nombre:</label>';
                    if(associatedJson===''){
                                bodyElement.innerHTML += '<input type="text" name="name" id="name" class="field-style">';}
                    else {
                        //TODO Cuando puedas editar, comprueba que el nombre se ve bien y no con %20 en lugar de espacio
                        bodyElement.innerHTML += '<input type="text" name="name" id="name" class="field-style" value=' + encodeURIComponent(associatedJson.name) + '>';
                    }
                bodyElement = document.getElementById("campos");
                bodyElement.innerHTML += '</div>';

                bodyElement.innerHTML += '<div class="fieldElement" id="fieldWiki">';
                bodyElement = document.getElementById("fieldWiki");
                    bodyElement.innerHTML += '<label for="wikiUrl" class="fieldTag">Wiki:</label>';
                    if(associatedJson==='') {
                        bodyElement.innerHTML += '<input type="url" name="wikiUrl" id="wikiUrl" class="field-style" placeholder="página de la wikipedia">';
                    } else {
                        bodyElement.innerHTML += '<input type="url" name="wikiUrl" id="wikiUrl" class="field-style" value=' + associatedJson.wikiUrl + '>';
                    }
                bodyElement = document.getElementById("campos");
                bodyElement.innerHTML += '</div>';

                bodyElement.innerHTML += '<div class="fieldElement" id="fieldPhoto">';
                bodyElement = document.getElementById("fieldPhoto");
                    bodyElement.innerHTML += '<label for="imageUrl" class="fieldTag">Foto:</label>';
                    if(associatedJson==='') {
                        bodyElement.innerHTML += '<input type="text" name="imageUrl" id="imageUrl" class="field-style" placeholder="página donde se aloja la imagen">';
                    } else {
                        bodyElement.innerHTML += '<input type="text" name="imageUrl" id="imageUrl" class="field-style" placeholder="página donde se aloja la imagen" value=' + associatedJson.imageUrl + '>';
                    }
                bodyElement = document.getElementById("campos");
                bodyElement.innerHTML += '</div>';


            bodyElement.innerHTML += '</fieldset>';
            bodyElement = document.getElementById("form");

            bodyElement.innerHTML += '<br/>';
            bodyElement.innerHTML += '<div id="formButton">';
                bodyElement = document.getElementById("formButton");
                bodyElement.innerHTML += '<input type="submit" value="Send" id="sendButton" class="transition" onclick="close()"/>';
            bodyElement.innerHTML += '</div>';
            bodyElement = document.getElementById("form");
        bodyElement.innerHTML += '</form>';
        //bodyElement.innerHTML += '</div>';

        $('#sendButton').click(function () {

            (function ($) {
                $.fn.serializeFormJSON = function () {
                    let o = {};
                    let a = this.serializeArray();
                    $.each(a, function () {
                        if (o[this.name]) {
                            if (!o[this.name].push) {
                                o[this.name] = [o[this.name]];
                            }
                            o[this.name].push(this.value || '');
                        } else {
                            o[this.name] = this.value || '';
                        }
                    });
                    return o;
                };
            })(jQuery);


            $("input[type='radio']").click(function(){
                tipo = $("input[type='radio']:checked").val();
                    console.log(tipo);
            });

            $('form').submit(function (e) {
                e.preventDefault();
                let data = $(this).serializeFormJSON();
                console.log(data);
                console.log(id);
                console.log(tipo);

               //let dataJSON  = JSON.parse(data);

             /*   if(data["birthDate"] === "") data["birthDate"]=null;
                if(data["deathDate"] === "") data["deathDate"]=null;*/

                console.log(data);
                if(id != -1) {

                    updateElement(data, id, tipo);
                }
                else {
                    console.log(data);
                    console.log(tipo);
                    createElement(data, tipo);
                }

        });
        });
        console.log(associatedJson);
    }

    /*function nullFormat($element, $data) {
        $.each($data =>{
            if ($attr  === 'bithDate' && ($date = DateTime::createFromFormat('!Y-m-d', $datum))) ? $element->setBirthDate($date) : null;
        ($attr  === 'deathDate' && ($date = DateTime::createFromFormat('!Y-m-d', $datum))) ? $element->setDeathDate($date) : null;
    })*/

    function createElement (info, category) {
        console.log(category);
        /*  console.log(info);
          delete info[id];
          console.log(info);*/

        console.log(info);
        if(info["birthDate"] === "") delete info["birthDate"];
        if(info["deathDate"] === "") delete info["deathDate"];
        console.log(info);


        jQuery.ajax({
            type: 'POST',
            url: 'http://127.0.0.1:8000/api/v1/' + category,
            headers: {"Authorization": authHeader},
            data: info,
            beforeSend: function(xhr){xhr.setRequestHeader('X-Test-Header', 'test-value');},
            success: function() { alert('Success!' + authHeader); },
            //contentType: 'application/json',

            dataType: 'json',
        });
    }

    function updateElement (info, id, category) {
        let tipo = "tipo";
        delete info[tipo];
       /* console.log(id);
        console.log(info);
        delete info[id];
        let tipo = "tipo";
        delete info[tipo];

        let fechaNacimiento = info["birthDate"];
        if(fechaNacimiento!==null) fechaNacimiento.toString();
        delete info["birthDate"];
        info["birthDate"]=fechaNacimiento;*/

        console.log(info.deathDate);
        /*let fechaMuerte = info["deathDate"];
        if(fechaMuerte==="") fechaMuerte.toString();
        delete info["deathDate"];
        info["deathDate"]=fechaMuerte;
        console.log (info);*/
        if(info["birthDate"] === "") delete info["birthDate"];
        if(info["deathDate"] === "") delete info["deathDate"];
        let myInfo = JSON.stringify(info);
        console.log(myInfo);
        let final = myInfo;
        final = final.replace (/["']["']/g,"");
        console.log(final);

       /* switch (category) {
            case "persons": {
                info["products"]=null;
                info["entities"]=null;
                break;
            }
            case "entities": {
                info.push({
                    products: null,
                    persons: null
                });
                break;
            }
            case "products": {
                info.push({
                    persons: null,
                    entities: null
                });
                break;
            }
        }*/
        console.log(info);

        jQuery.ajax({
            url: 'http://127.0.0.1:8000/api/v1/' + category + '/' + id,
            headers: {"Authorization": authHeader},
            type: 'PUT',
            data: myInfo,
            contentType: 'application/json',
            success: function(data) {
                console.log(info);
            }
        });
    }