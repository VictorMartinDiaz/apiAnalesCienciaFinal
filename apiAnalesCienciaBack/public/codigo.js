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

    async function cerrar() {
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

    async function request() {
        $('#entities').slideUp();
        $('#persons').slideUp();
        $('#products').slideUp();

        $('form').bind('submit', function () {
            document.getElementById("boton").disabled = true;
            $.ajax({
                type: 'POST',
                url: '/access_token',
                data: $('form').serialize(),
                success: function (data, textStatus, request) {
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
                data['users'].forEach(i =>
                {
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
                        bodyElement.innerHTML += '<a class="btn btn-primary" id="crear" onclick="newElement()">Crear</a>';
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
        $.ajax({
            type: 'GET',
            url: 'api/v1/persons',
            headers: {"Authorization": authHeader},
            dataType: 'json',
            success: function (data) {
                personasElement.innerHTML = '';
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
                entidadesElement.innerHTML = '';
                data['entities'].forEach(i => {
                    {
                        entidadesElement.innerHTML +=
                            '<span class="dropdown-item" id="' + i['entity'].name + '">' +
                            '<img class="dropdownImg" src=' + i['entity'].imageUrl + ' />' +
                            '<span class="name" id="' + i['entity'].name + '">' + i['entity'].name +
                            '</span>' +
                            '</span>'
                        let enviar = JSON.stringify(data);
                        //crea elementos tipo paco:{json}, juan:{json}, etc...
                        window.localStorage.setItem(i['entity'].name, enviar);
                    }
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
                productosElement.innerHTML = '';
                data['products'].forEach(i => {
                    productosElement.innerHTML +=
                        '<span class="dropdown-item" id="' + i['product'].name + '">' +
                        '<img class="dropdownImg" src=' + i['product'].imageUrl+ ' />' +
                        '<span class="name" id="' + i['product'].name + '">' + i['product'].name +
                        '</span>' +
                        '</span>';
                    let enviar = JSON.stringify(data);
                    //crea elementos tipo paco:{json}, juan:{json}, etc...
                    window.localStorage.setItem(i['product'].name, enviar);
                });
            },
        });
    }

    async function localizarElemento(elemento, tipo, name) {
        let dibujar = "";
        let i = 0;
        for (let found = false; i <= localStorage.length - 1 && !found; i++) {
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
        $('#persons').slideUp();
        $('#entities').slideUp();
        $('#products').slideUp();

        document.getElementById("ficha").innerHTML = "";
        await abrir();
        rellenarFicha(tipo, dibujar);
    }

    function rellenarFicha(tipo, dibujar){
        let usuarioRegistrado = localStorage.getItem('usuarioRegistrado');
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
        let data = [dibujar];
        document.getElementById("ficha").innerHTML += (json2html.transform(data, template));

        if (usuarioRegistrado === "writer") {
            bodyElement.innerHTML += '<div>';
            bodyElement.innerHTML += '<button class="btn btn-info transition" rel="pop-up" id="editar">Editar</button>';
            bodyElement.innerHTML += '<button class="btn btn-warning" rel="pop-up" id="eliminar">Eliminar</button>';
            bodyElement.innerHTML += '</div>';
        }
        $('#eliminar').click(async function () {
            await eliminar(dibujar, tipo);
        });
        $('#editar').click(async function () {
            $("#ficha").toggle({ effect: "scale", direction: "horizontal" });
            await new Promise(r => setTimeout(r, 300));
            createForm(dibujar, dibujar.id, tipo);
            $("#ficha").toggle({ effect: "scale", direction: "horizontal" });
        });
    }

    function deleteFromDB(id, category){
        jQuery.ajax({
            url: 'http://127.0.0.1:8000/api/v1/' + category + '/' + id,
            headers: {"Authorization": authHeader},
            type: 'DELETE',
            success: function(data) {
            }
        });
    }

    async function eliminar(associatedJson, category) {
        console.log(localStorage);
        localStorage.removeItem(associatedJson.name);
        document.getElementById(associatedJson.name).innerHTML='';
        deleteFromDB(associatedJson.id, category);
        await cerrar();
    }

    function newElement() {
        let emptyJson = {'name': '','birthDate': '','deathDate': '','wikiUrl': '','imageUrl': ''};
        pintarFicha('', '', emptyJson).then(r => createForm('', -1, "person"));
    }

    function createForm(associatedJson, id, tipo) {
        console.log(associatedJson);
        let bodyElement = document.getElementById("ficha");
            bodyElement.innerHTML = '';
            bodyElement.innerHTML += '<button class="btn btn-danger" rel="pop-up" id="cerrar" onclick="cerrar();">X</button>';
        $('#ficha').addClass("form-style");
        $('#ficha').removeClass("ficha");

        bodyElement.innerHTML += '<form id="form">';
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
                    if(tipo==="persons" || tipo===""){
                        bodyElement.innerHTML += '<input type="radio" id="persona" name="tipo" value="person" checked>';
                    }
                    else {
                        bodyElement.innerHTML += '<input type="radio" id="persona" name="tipo" value="person">';
                    }
                    bodyElement.innerHTML += '<label for="persona" class="radioLabel">Persona</label><br>';
                bodyElement.innerHTML += '</div>';
                bodyElement = document.getElementById("radioBotones");

                bodyElement.innerHTML += '<div class="radioElement" id="radioEntidad">';
                    bodyElement = document.getElementById("radioEntidad");
                    if(tipo==="entities") {
                        bodyElement.innerHTML += '<input type="radio" id="entidad" name="tipo" value="entity" checked>';
                    }
                    else {
                        bodyElement.innerHTML += '<input type="radio" id="entidad" name="tipo" value="entity">';
                    }
                    bodyElement.innerHTML += '<label for="entidad" class="radioLabel">Entidad</label><br>';
                bodyElement.innerHTML += '</div>';
                bodyElement = document.getElementById("radioBotones");

                bodyElement.innerHTML += '<div class="radioElement" id="radioProducto">';
                bodyElement = document.getElementById("radioProducto");
                if(tipo==="products"){
                    bodyElement.innerHTML += '<input type="radio" id="producto" name="tipo" value="product" checked>';
                }
                else {
                    bodyElement.innerHTML += '<input type="radio" id="producto" name="tipo" value="product">';
                }
                bodyElement.innerHTML += '<label for="producto" class="radioLabel">Producto</label><br>';
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
                        if(associatedJson.birthDate==='' || associatedJson.birthDate===null || associatedJson.birthDate===undefined){
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
                        if(associatedJson.deathDate==='' || associatedJson.deathDate===null || associatedJson.deathDate===undefined){
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
                        bodyElement.innerHTML += '<input type="text" name="name" id="name" class="field-style" value=' + encodeURIComponent(associatedJson.name).replace("%20", " ") + '>';
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

        $('input[name ="tipo"]').click(function () {
            tipo = this.value;
            console.log(tipo);
        })

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

            $("div[class='radioElement']").click(function(){
                tipo = $("input[type='radio']:checked").val();
                    console.log(tipo);
            });
/*
            switch(tipo) {
                case "persons": {
                    associatedJson["entities"] = '';
                    associatedJson["products"] = '';
                    delete associatedJson["persons"];
                    break;
                }
                case "entities": {
                    associatedJson["persons"] = '';
                    associatedJson["products"] = '';
                    delete associatedJson["entities"];
                    break;
                }
                case "products": {
                    associatedJson["entities"] = '';
                    associatedJson["persons"] = '';
                    delete associatedJson["products"];
                    break;
                }
            }
            console.log(associatedJson);*/

            $('form').submit(function (e) {
                e.preventDefault();
                let data = $(this).serializeFormJSON();
                console.log(data);
                console.log(id);
                console.log(tipo);

                if(data["birthDate"] === "") delete data["birthDate"];
                if(data["deathDate"] === "") delete data["deathDate"];

                console.log(data);
                if(id != -1) {
                    console.log(tipo);
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

    function createElement (info, category) {

        console.log(info);
        console.log(category);

        switch (category) {
            case "person": {
                category = "persons";
                break;
            }
            case "product": {
                category = "products";
                break;
            }
            case "entity": {
                category = "entities";
                break;
            }
        }

            console.log(category);

            let tipo = "tipo";
            delete info[tipo];
            let myInfo = JSON.stringify(info);
            console.log(myInfo);
            jQuery.ajax({
                type: 'POST',
                url: 'http://127.0.0.1:8000/api/v1/' + category,
                headers: {"Authorization": authHeader},
                data: myInfo,
                contentType: 'application/json',
                success: function() {
                    switch (category) {
                        case "persons": {
                            retrievePersons();
                            break;
                        }
                        case "products": {
                            retrieveProducts();
                            break;
                        }
                        case "entities": {
                            retrieveEntities();
                            break;
                        }
                    }
                    cerrar().then(r => confirm(info["name"] + " ha sido creado"));
                    //TODO LocalStorage
                    },
                dataType: 'json',
            });
    }

    function updateElement (info, id, category) {
        console.log(category);
        let tipo = "tipo";
        delete info[tipo];
        let myInfo = JSON.stringify(info);
        jQuery.ajax({
            url: 'http://127.0.0.1:8000/api/v1/' + category + '/' + id,
            headers: {"Authorization": authHeader},
            type: 'PUT',
            data: myInfo,
            contentType: 'application/json',
            success: function(data) {
                switch (category) {
                    case "persons": {
                        retrievePersons();
                        break;
                    }
                    case "products": {
                        retrieveProducts();
                        break;
                    }
                    case "entities": {
                        retrieveEntities();
                        break;
                    }
                }
                 cerrar().then(r => confirm(info["name"] + " ha sido actualizado"));
                 //TODO LocalStorage
            }
        });
    }