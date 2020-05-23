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
            height: '600px',
            width: '600px',
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
                        let tipo = i['user'].role;
                        let suspendido = i['user'].standby;
                        localStorage.usuarioRegistrado = tipo;
                        imIn(tipo, suspendido);
                        return false;
                    }
                })
            },
        })
    }

    function imIn(tipo, suspendido) {
        try {
            //let usuarioRegistrado = localStorage.getItem('usuarioRegistrado');

            if ((tipo === "reader" && suspendido===false)|| tipo === "writer") {
                let bodyElement = "";
                $("#ficha").click(function () {
                    document.getElementById("logout").style.visibility = "visible";
                });

                bodyElement = document.getElementById("botonera");
                bodyElement.innerHTML = "<button class='btn btn-danger' id='logout'>Logout</button>";
                if (tipo === "writer") {
                    bodyElement.innerHTML += '<a class="btn btn-primary" id="crear" onclick="newElement()">Crear</a>';
                    bodyElement.innerHTML += '<a class="btn btn-light text-secondary" id="crear" onclick="retrieveUsers()">Permisos</a>';
                }
                $("#logout").click(function () {
                    localStorage.removeItem('usuarioRegistrado');
                    location.reload();
                });
            } else if(tipo === "reader" && suspendido===true){
                window.alert("Este usuario esta suspendido");
                location.reload();
            }
        } catch (error) {
            console.log(error)
        }
    }

    function retrieveUsers() {
        if($('#ficha').hasClass("form-style")){
            $('#ficha').removeClass("form-style");
            $('#ficha').addClass("ficha");
        }
        let usersElement = document.getElementById("ficha");
        $.ajax({
            type: 'GET',
            url: 'api/v1/users',
            headers: {"Authorization": authHeader},
            dataType: 'json',
            success: async function (data) {
               await pintaListaUsuarios(usersElement, data);
            },
        });
        $("#editarPermisos").click(function () {

        });

    }

    async function  promoteUser(userId) {
        userId = recortar(userId);
        let miJson = localStorage.getItem(userId)
        miJson = JSON.parse(miJson);

        let dato = '';
        if(miJson["standby"]===false)
            dato = {'role': 'writer'};
        if(miJson["standby"]===true)
            dato = {'standby': 0};


        $.ajax({
            type: 'PUT',
            url: 'api/v1/users/'+userId,
            headers: {"Authorization": authHeader},
            dataType: 'json',
            data: dato,
            success: async function (data) {

                await cerrar();
                await new Promise(r => setTimeout(r, 50));
                retrieveUsers();
                await abrir();
            },
        })
    }

    function  suspendUser(userId) {
            userId = recortar(userId);

            $.ajax({
                type: 'PUT',
                url: 'api/v1/users/'+userId,
                headers: {"Authorization": authHeader},
                dataType: 'json',
                data: {"standby": 1},
                success: async function (data) {

                    await cerrar();
                    await new Promise(r => setTimeout(r, 50));
                    retrieveUsers();
                    await abrir();
                },
            });
    }

    function  deleteUser(userId) {
        userId = recortar(userId);
         $.ajax({
             type: 'DELETE',
             url: 'api/v1/users/'+userId,
             headers: {"Authorization": authHeader},
             dataType: 'json',
             //data: dato,
             success: async function (data) {
                 window.alert("Usuario borrado");
                 await cerrar();
                 await new Promise(r => setTimeout(r, 100));
                 retrieveUsers();
                 await abrir();
             },
         })
    }

    function recortar(userId) {
        (userId).toString();
        userId = userId.slice(0, -1);
        userId = userId.substr(2);
       return userId;
    }

    async function pintaListaUsuarios(usersElement, data) {
        usersElement.innerHTML = '';
        await abrir();
        usersElement.innerHTML +=
            '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>' +

            '<div class="half">' +
                '<h1 class="formTag">Escritores</h1>' +
                '<hr class="bajoLinea"/>' +
                '<div class="tbl-header">' +
                    '<table>' +
                        '<thead>' +
                            '<tr>' +
                                '<th><h1>id</h1></th>' +
                                '<th><h1>nombre</h1></th>' +
                                '<th><h1>e-mail</h1></th>' +
                            '</tr>' +
                        '</thead>' +
                    '</table>' +
                '</div>' +
                '<div class="tbl-content">' +
                    '<table>' +
                        '<tbody id="userWriter"></tbody>' +
                    '</table>' +
                '</div>' +
            '</div>' +

            '<div class="half">' +
                '<h1 class="formTag">Lectores</h1>' +
                '<hr class="bajoLinea"/>' +
                '<div class="tbl-header">' +
                    '<table>' +
                        '<thead>' +
                            '<tr>' +
                                '<th><h1>id</h1></th>' +
                                '<th><h1>nombre</h1></th>' +
                                '<th><h1>e-mail</h1></th>' +
                                '<th><h1>editar</h1></th>' +
                                '<th><h1>suspender</h1></th>' +
                                '<th><h1>eliminar</h1></th>' +
                            '</tr>' +
                        '</thead>' +
                    '</table>' +
                '</div>' +
                '<div class="tbl-content">' +
                    '<table>' +
                        '<tbody id="userReader"></tbody>' +
                    '</table>' +
                '</div>' +
            '</div>' ;
        let usersWriter = '';
        let usersReader = '';
        data['users'].forEach(i => {

            usersWriter = document.getElementById("userWriter");
            if(i['user'].role==="writer") {
                usersWriter.innerHTML +=
                    '<tr>' +
                        '<td>' + i["user"].id + '</td>' +
                        '<td>' + i["user"].username + '</td>' +
                        '<td>' + i["user"].email + '</td>' +
                    '</tr>';
            }
            if(i['user'].role==="reader"){
                let userId = JSON.stringify(i["user"]);
                window.localStorage.setItem(i['user'].id, userId);
                usersReader = document.getElementById("userReader");
                usersReader.innerHTML +=
                    '<tr id="i\'' + i["user"].id  + '\'">' +
                        '<td>' + i["user"].id + '</td>' +
                        '<td>'+ i["user"].username +'</td>' +
                        '<td>' + i["user"].email + '</td>' +
                        '<td><button class="btn btn-info " rel="pop-up" id="u\'' + i["user"].id  + '\'" onclick="promoteUser(this.id);">Promote</button></td>' +
                        '<td><button class="btn btn-warning" rel="pop-up" id="s\'' + i["user"].id  + '\'" onclick="suspendUser(this.id);">Demote</button></td>' +
                        '<td><button class="btn btn-danger" rel="pop-up" id="d\'' + i["user"].id  + '\'" onclick="deleteUser(this.id);">Delete</button></td>' +
                    '</tr>';

                //Pintamos la clase si esta o no en suspension
                    let tempId = "i'"+ i["user"].id+"'";
                    tempId = document.getElementById(tempId);
                    if(i["user"].standby===true)
                        tempId.classList.add("standby");
                    if(i["user"].standby===false)
                        tempId.classList.remove("standby");
            }

            let enviar = JSON.stringify(data);
            //crea elementos tipo paco:{json}, juan:{json}, etc...

        });
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
                        '<span class="dropdown-item" id="'+ i['person'].name + '">' +
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
            switch (tipo) {
                case 'persons': {
                    if (dibujar['persons'][i]['person'].name === name) {
                        found = true;
                        dibujar = dibujar['persons'][i]['person'];
                    }
                    break;
                }
                case 'entities': {
                    if (dibujar['entities'][i]['entity'].name === name) {
                        found = true;
                        dibujar = dibujar['entities'][i]['entity'];
                    }
                    break;
                }
                case 'products': {
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
        bodyElement.innerHTML = '';
        bodyElement.innerHTML +=
            '<div id="rel_close_list">' +
            '<button class="btn btn-secondary" id="listaRel"><i class="fas fa-project-diagram"></i></button>' +
            '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>' +
            '</div>';


        bodyElement = document.getElementById("ficha");




        let data = [dibujar];
        document.getElementById("ficha").innerHTML += (json2html.transform(data, template));

        if (usuarioRegistrado === "writer") {
            bodyElement.innerHTML += '<div class="botoneraFicha">' +
            '<button class="btn btn-info transition" rel="pop-up" id="editar">Editar</button>' +
            '<button class="btn btn-warning" rel="pop-up" id="eliminar">Eliminar</button>' +
            '</div>';
        }
        $('#eliminar').click(async function () {
            await eliminar(dibujar, tipo);
        });
        $('#editar').click(async function () {
            $("#ficha").toggle({ effect: "scale", direction: "horizontal" });
            await new Promise(r => setTimeout(r, 300));
            createForm(dibujar, dibujar.id, tipo, true);
            $("#ficha").toggle({ effect: "scale", direction: "horizontal" });
        });

        $('#listaRel').click(function () {
            //tipo = (products|persons|entities);
            //dibujar = JSON del elemento mostrado en la ficha
            listRelations(tipo, dibujar);
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
        localStorage.removeItem(associatedJson.name);
        document.getElementById(associatedJson.name).innerHTML='';
        deleteFromDB(associatedJson.id, category);
        await cerrar();
    }

    function newElement() {
        let emptyJson = {'name': '','birthDate': '','deathDate': '','wikiUrl': '','imageUrl': ''};
        pintarFicha('', '', emptyJson).then(r => createForm('', -1, "person", false));
    }

    function createForm(associatedJson, id, tipo, edit) {

        let bodyElement = document.getElementById("ficha");
            bodyElement.innerHTML = '';
            bodyElement.innerHTML += '<div id="rel_close">';
            bodyElement = document.getElementById("rel_close");
            bodyElement.innerHTML += '<button class="btn btn-secondary" id="relaciones"><i class="fas fa-project-diagram"></i></button>';
            bodyElement.innerHTML += '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>';
            bodyElement.innerHTML += '</div>';
        bodyElement = document.getElementById("ficha");
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
                if(edit){
                    document.getElementById("persona").disabled = true;
                    document.getElementById("entidad").disabled = true;
                    document.getElementById("producto").disabled = true;
                }

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
        })

        $('#relaciones').click(function () {
            editRelations(tipo, associatedJson);
        });

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
            });

            $('form').submit(function (e) {
                e.preventDefault();
                let data = $(this).serializeFormJSON();
                if(data["birthDate"] === "") delete data["birthDate"];
                if(data["deathDate"] === "") delete data["deathDate"];

                if(id !== -1)
                    updateElement(data, id, tipo);
                else
                    createElement(data, tipo);
            });
        });
    }

    function createElement (info, category) {
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
            let tipo = "tipo";
            delete info[tipo];
            let myInfo = JSON.stringify(info);
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
                    cerrar().then(r => confirm(
                        info["name"] + " ha sido creado"));
                    },
                dataType: 'json',
            });
    }

    function updateElement (info, id, category) {
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
                 cerrar().then(r => confirm(
                     info["name"] + " ha sido actualizado"));
            }
        });
    }

    function listRelations(tipo, dibujar){

        switch (tipo) {
            case("entities"): {
                card4entities(dibujar).then(r => {});
                break;
            }
            case("products"): {
                card4products(dibujar);
                break;
            }
            case("persons"): {
                card4persons(dibujar);
                break;
            }
        }
    }

    //TODO, acaba esto (No esta funcionando) y has terminado

    async function card4entities(dibujar) {
        let ficha = document.getElementById("ficha");
        ficha.innerHTML = '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>' +
            '<h1>Relaciones</h1>';
        ficha.innerHTML +=
            '<div class="half" id="productCardRelation1">' +
            '<h1>Autores</h1>' +
            buscaPersona(dibujar, 1) +
            '</div>' +
            '<div class="half" id="productCardRelation2">' +
            '<h1>Productos</h1>' +
                buscaProducto(dibujar, 2) +
            '</div>';

    }

    function card4products(dibujar) {
        let ficha = document.getElementById("ficha");
        ficha.innerHTML = '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>' +
            '<h1>Relaciones</h1>';
            ficha.innerHTML +=
            '<div class="half" id="productCardRelation1">' +
                '<h1>Autores</h1>' +
                buscaPersona(dibujar, 1) +
            '</div>' +
            '<div class="half" id="productCardRelation2">' +
                '<h1>Entidades</h1>' +
                buscaEntidad(dibujar, 2) +
            '</div>';

    }

    function card4persons(dibujar) {
        let ficha = document.getElementById("ficha");
        ficha.innerHTML = '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>' +
            '<h1>Relaciones</h1>';
        ficha.innerHTML +=
            '<div class="half" id="productCardRelation1">' +
                '<h1>Productos</h1>' +
                buscaProducto(dibujar, 1) +
            '</div>' +
            '<div class="half" id="productCardRelation2">' +
                '<h1>Entidades</h1>' +
                buscaEntidad(dibujar, 2) +
            '</div>';
    }

    function buscaPersona(dibujar, pos) {
        for(let i in dibujar["persons"])
        {
            $.ajax({
                type: 'GET',
                url: 'api/v1/persons/' + dibujar["persons"][i],
                headers: {"Authorization": authHeader},
                dataType: 'json',
                success: function (data) {
                    dibujaRel(data['person'].name, data['person'].imageUrl, pos, "persons", data['person']).then(r => {});
                },
                /*error: function (jqXHR, textStatus, errorThrown) {
                    dibujar["persons"].splice(dibujar["persons"][i], 1); }*/
            });
        }
        return "";
    }
    function buscaEntidad(dibujar, pos) {
        for(let i in dibujar["entities"])
        {
            $.ajax({
                type: 'GET',
                url: 'api/v1/entities/' + dibujar["entities"][i],
                headers: {"Authorization": authHeader},
                dataType: 'json',
                success: function (data) {
                    dibujaRel(data['entity'].name, data['entity'].imageUrl, pos, "entities", data['entity']).then(r => {});
                },
                /*error: function (jqXHR, textStatus, errorThrown) {
                    dibujar["entities"].splice(dibujar["entities"][i], 1); }*/
            });
        }
        return "";
    }
    function buscaProducto(dibujar, pos) {
        for(let i in dibujar["products"])
        {
            $.ajax({
                type: 'GET',
                url: 'api/v1/entities/' + dibujar["products"][i],
                headers: {"Authorization": authHeader},
                dataType: 'json',
                success: function (data) {
                    dibujaRel(data['product'].name, data['product'].imageUrl, pos, "products", data['product']).then(r => {});
                },
                /*error: function (jqXHR, textStatus, errorThrown) {
                    dibujar["products"].splice(dibujar["products"][i], 1); }*/
            });
        }
        return "";
    }

    async function dibujaRel(name, imageUrl, pos, tipo, dibujar){
        await new Promise(r => setTimeout(r, 100));
        let mitad = document.getElementById("productCardRelation"+pos);
        let cleanName = name.replace(/ /g,'')
        let id = "dr"+cleanName;
            mitad.innerHTML +=
                '<span class="listado">' +
                '<div id="'+ id + '">' +
                '<img src="' + imageUrl + '" class="dropdownImg"/>' +
                '<p>' + name + '</p>' +
                '</div>' +
                '</span>';

        $('#'+ id +'').click(async function () {
            await cerrar();
            await new Promise(r => setTimeout(r, 50));
            await pintarFicha(name, tipo, dibujar);
            await abrir();

        });
    }


    //TODO editar relaciones EMPEZAR AQUI MAÑANA
    function editRelations(tipo, dibujar){
        console.log("editando");
        console.log(tipo);
        console.log(dibujar);
    }

    //TODO altas usuarios nuevos