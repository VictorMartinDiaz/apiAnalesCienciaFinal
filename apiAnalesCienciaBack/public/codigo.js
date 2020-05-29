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
        document.getElementById("ficha").innerHTML = '';
        $("#ficha").animate({
            opacity: '0',
            height: '0px',
            width: '0px',
        });
        await new Promise(r => setTimeout(r, 300));
        $('.logoCentral').removeClass("escondida");
        $('.banda')
            .removeClass("escondida")
            .animate({
            opacity: '0.87'
        });
        $('.nav-link').show();
    }

    //abrimos la ficha con un tamaño y color de fondos determinados en función de lo que queramos mostrar
    async function abrir(size, color){
        $('#persons').slideUp();
        $('#products').slideUp();
        $('#entities').slideUp();
        if ($(window).width() < 767) {
            $('.nav-link').slideUp();
        }
        $('.banda')
            .addClass("escondida")
            .animate({
                opacity: '0'
            });
        $('.logoCentral').addClass("escondida");
        await new Promise(r => setTimeout(r, 300));
        $("#ficha").show()
            .css("background-color", color)
            .animate({
            opacity: '1',
            height: size,
            width: size,
        });
    }

    //El bloque de botones para hacer log-in/sign-in
    function PreRequest(){
        $("#preReqBlockOpen").show();
        $("#preReqBlockClose").hide();
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

    //No hemos conseguido que funcione el tema de las fechas del cumpleaños
    function altaUser(userInfo) {
            //let conFecha = userInfo;
            //delete userInfo["birthday"];
            console.log(userInfo);
            let info = JSON.stringify(userInfo);
            console.log(info);
         $.ajax({
                type: 'POST',
                url: 'api/v1/users',
                data: userInfo,
                //dataType: 'json',
                success: function (data) {
                    console.log("Success");
                }, error: function (xhr, status, error) {
                    let err = eval("(" + xhr.responseText + ")");
                    if(err.code===400)
                        alert("El usuario o el correo introducidos ya existen");
             }
            })

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
                        let usuario = i['user'];
                        localStorage.usuarioRegistrado = i['user'].role;
                        imIn(usuario);
                        return false;
                    }
                })
            },
        })
    }

    //"Estoy dentro!"
    function imIn(usuario) {
        let tipo = usuario.role;
        let suspendido = usuario.standby;
        try {
            //let usuarioRegistrado = localStorage.getItem('usuarioRegistrado');
            let bodyElement = "";
            if ((tipo === "reader" && suspendido===false)|| tipo === "writer") {
                $("#ficha").click(function () {
                    document.getElementById("logout").style.visibility = "visible";
                });
                bodyElement = document.getElementById("botonera");
                bodyElement.innerHTML = "<button class='btn btn-danger' id='logout'>Logout</button>" +
                                         '<a class="btn btn-dark" id="crear">Edit Info</a>';
                if (tipo === "writer") {
                    bodyElement.innerHTML += '<a class="btn btn-primary" id="crear" onclick="newElement()">Crear</a>';
                    bodyElement.innerHTML += '<a class="btn btn-light text-secondary" id="crear" onclick="retrieveUsers()">Permisos</a>';
                }
                $("#logout").click(function () {
                    localStorage.removeItem('usuarioRegistrado');
                    location.reload();
                });
            } else if(tipo === "reader" && suspendido===true) {
                window.alert("Este usuario esta suspendido");
                location.reload();
            }
        } catch (error) {
            console.log(error)
        }

        $("#crear").click(async function () {
            console.log(usuario);
            await createUser(usuario);
        })
    }

    function retrieveUsers() {
        /*if($('#ficha').hasClass("form-style")){
            $('#ficha').removeClass("form-style")
            .addClass("ficha");
        }*/
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
                let color = "rgba(12,43,62,0.92)";
                await abrir("600", color);
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
                let color = "rgba(12,43,62,0.92)";
                await abrir("600", color);
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
                 let color = "rgba(12,43,62,0.92)";
                 await abrir("600", color);
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
        let color = "rgba(12,43,62,0.92)";
        await abrir("600", color);
        usersElement.innerHTML +=
            '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>' +

            '<div class="half" id =table1stHalf>' +
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
            '<div class="half" id="table2ndHalf">' +
                '<h1 class="formTag">Lectores</h1>' +
                '<hr class="bajoLinea"/>' +
                '<div class="tbl-header header2">' +
                    '<table>' +
                        '<thead>' +
                            '<tr>' +
                                '<th><h1>id</h1></th>' +
                                '<th><h1>nombre</h1></th>' +
                                '<th><h1>e-mail</h1></th>' +
                                '<th><h1></h1></th>' +
                                '<th><h1></h1></th>' +
                                '<th><h1></h1></th>' +
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
        /*if($('#ficha').hasClass("form-style")){
            $('#ficha').removeClass("form-style");
            $('#ficha').addClass("ficha");
        }*/
        $('#persons').slideUp();
        $('#entities').slideUp();
        $('#products').slideUp();

        document.getElementById("ficha").innerHTML = "";
        let color = "rgba(82,82,82,0.92)";
        await abrir("600", color);
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
                '<>': 'a', 'href': '${wikiUrl}', 'html': 'wikipedia', 'class': 'linkWiki', 'target': '_blank'
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
            await createForm(dibujar, dibujar.id, tipo, true);
            //$("#ficha").toggle({ effect: "scale", direction: "horizontal" });
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

    async function createForm(associatedJson, id, tipo, edit) {

        let bodyElement = document.getElementById("ficha");
            bodyElement.innerHTML = '';
            bodyElement.innerHTML += '<div id="rel_close">';
            bodyElement = document.getElementById("rel_close");
            bodyElement.innerHTML += '<button class="btn btn-secondary" id="relaciones"><i class="fas fa-project-diagram"></i></button>';
            bodyElement.innerHTML += '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>';
            bodyElement.innerHTML += '</div>';
        bodyElement = document.getElementById("ficha");
        /*$('#ficha').addClass("form-style");
        $('#ficha').removeClass("ficha");*/
        let color = "rgba(209,212,217,0.92)";
        await abrir(600, color);

        //No se pueden crear relaciones a un elemento que no existe. Escondemos la posibilidad
        if(id===-1) {$("#relaciones").hide();}

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
        console.log(dibujar);
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

    //TODO, la mitad los elementos en cada listado fallan dios sabe por qué
    //TODO La ficha de relaciones no carga a la primera si se le meten muchas relaciones. Si se le llama una segunda vez, carga

    async function card4entities(dibujar) {
        let ficha = document.getElementById("ficha");
        ficha.innerHTML = '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>' +
            '<h1>Relaciones</h1>';
        ficha.innerHTML +=
            '<hr class="separador">' +
            '<div class="half" id="productCardRelation1">' +
                '<h1>Autores</h1>' +
                buscaPersona(dibujar, 1) +
            '</div>' +
            '<hr class="separador">' +
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
            '<hr class="separador">' +
            '<div class="half" id="productCardRelation1">' +
                '<h1>Autores</h1>' +
                buscaPersona(dibujar, 1) +
            '</div>' +
            '<hr class="separador">' +
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
            '<hr class="separador">' +
            '<div class="half" id="productCardRelation1">' +
                '<h1>Productos</h1>' +
                buscaProducto(dibujar, 1) +
            '</div>' +
            '<hr class="separador">' +
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
            console.log(dibujar["products"][i]);
            $.ajax({
                type: 'GET',
                url: 'api/v1/products/' + dibujar["products"][i],
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

        $('#'+id+'').click(async function () {
            await cerrar();
            mitad.innerHTML = '';
            await new Promise(r => setTimeout(r, 50));
            await pintarFicha(name, tipo, dibujar);
            let color = "rgba(78,34,49,0.92)";
            await abrir("600", color);

        });
    }

    function editRelations(tipo, dibujar){
        let personsRetrieved = false;
        let personsRelatedArray = [];
        let productsRetrieved = false;
        let productsRelatedArray = [];
        let entitiesRetrieved = false;
        let entitiesRelatedArray = [];

        //tipo = (products|entities|persons)
        console.log(tipo);
        //El JSON completo
        console.log(dibujar);
        //estos tienen que entrar ckecked
        console.log(dibujar["entities"]);
        console.log(dibujar["persons"]);

        switch (tipo) {
            case("persons"): {
                if(dibujar["products"]!==null) productsRelatedArray = dibujar["products"].slice();
                if(dibujar["entities"]!==null) entitiesRelatedArray = dibujar["entities"].slice();
                break;
            }
            case("entities"): {
                if(dibujar["products"]!==null) productsRelatedArray = dibujar["products"].slice();
                if(dibujar["persons"]!==null) personsRelatedArray = dibujar["persons"].slice();
                break;
            }
            case("products"): {
                if(dibujar["entities"]!==null) entitiesRelatedArray = dibujar["entities"].slice();
                if(dibujar["persons"]!==null) personsRelatedArray = dibujar["persons"].slice();
                console.log("----------------------------");
                console.log(entitiesRelatedArray);
                console.log(personsRelatedArray);
                break;
            }
        }

        let mId = '';
        let ficha = document.getElementById("ficha");
        let miJson = '';
        ficha.innerHTML = '';
        ficha.innerHTML += '<button class="btn btn-danger" id="cerrar" onclick="cerrar();">X</button>';

        for (let x=0; x<=localStorage.length-1; x++) {
            miJson = localStorage.key(x);
            miJson = localStorage.getItem(miJson);

            //Dibujamos las personas
            if(miJson.substr(0, 7)==='{"perso' && !personsRetrieved && tipo!=="persons") {
                let k=0;
                let miJsonTemp = JSON.parse(miJson);
                ficha.innerHTML += '<div class="editRelations" id="autRel">' +
                '<h3>Autores</h3>';

                for (let i = 0; i < miJsonTemp["persons"].length; i++) {
                    mId = miJsonTemp["persons"][i]["person"]["id"];
                    mId = mId.toString();
                    mId = "per"+mId;
                    if(dibujar["persons"]===null){
                        dibujar["persons"]=[];
                    }

                    if ((dibujar["persons"][k] !== miJsonTemp["persons"][i]["person"]["id"])) {
                        document.getElementById("autRel").innerHTML +=
                            '<div class="custom-control custom-checkbox">' +
                            '<input type="checkbox" class="custom-control-input" id="' + mId + '">' +
                            '<label class="custom-control-label" for="' + mId + '">' + miJsonTemp["persons"][i]["person"]["name"] + '</label>' +
                            '<br/>' +
                            '</div>';
                    }
                    else  {
                        console.log((dibujar["persons"][k]));
                        document.getElementById("autRel").innerHTML +=
                            '<div class="custom-control custom-checkbox">' +
                            '<input type="checkbox" class="custom-control-input" id="' + mId + '" checked>' +
                            '<label class="custom-control-label" for="' + mId + '">' + miJsonTemp["persons"][i]["person"]["name"] + '</label>' +
                            '<br/>' +
                            '</div>';
                        console.log(k);
                        k++;
                    }
                }
                ficha.innerHTML += '</div>';
                personsRetrieved = true;
            }

            //Dibujamos los productos
            if(miJson.substr(0, 7)==='{"produ' && !productsRetrieved &&tipo!=="products"){
                let k=0;
                let miJsonTemp = JSON.parse(miJson);
                ficha.innerHTML += '<div class="editRelations" id="prodRel">' +
                    '<h3>Productos</h3>';

                for(let i=0; i<miJsonTemp["products"].length; i++) {
                    mId = miJsonTemp["products"][i]["product"]["id"];
                    mId = mId.toString();
                    mId = "pro" + mId;
                    if(dibujar["products"]===null){
                        dibujar["products"]=[];
                    }

                    if (dibujar["products"][k] !== miJsonTemp["products"][i]["product"]["id"]) {
                        document.getElementById("prodRel").innerHTML +=
                            '<div class="custom-control custom-checkbox">' +
                            '<input type="checkbox" class="custom-control-input" id="' + mId + '">' +
                            '<label class="custom-control-label" for="' + mId + '">' + miJsonTemp["products"][i]["product"]["name"] + '</label>' +
                            '<br/>' +
                            '</div>';
                    }
                    else  {
                        console.log((dibujar["products"][k]));
                        document.getElementById("prodRel").innerHTML +=
                            '<div class="custom-control custom-checkbox">' +
                            '<input type="checkbox" class="custom-control-input" id="' + mId + '" checked>' +
                            '<label class="custom-control-label" for="' + mId + '">' + miJsonTemp["products"][i]["product"]["name"] + '</label>' +
                            '<br/>' +
                            '</div>';
                        console.log(k);
                        k++;
                    }
                }
                ficha.innerHTML += '</div>';
                productsRetrieved = true;
            }

            //Dibujamos las entidades
            if(miJson.substr(0, 7)==='{"entit' && !entitiesRetrieved && tipo!=="entities"){
                let k=0;
                let miJsonTemp = JSON.parse(miJson);
                console.log(miJsonTemp);
                ficha.innerHTML += '<div class="editRelations" id="entRel">' +
                '<h3>Entidades</h3>';

                for(let i=0; i<miJsonTemp["entities"].length; i++) {
                    mId = miJsonTemp["entities"][i]["entity"]["id"];
                    mId = mId.toString();
                    mId = "ent"+mId;
                    if(dibujar["entities"]===null){
                        dibujar["entities"]=[];
                    }

                     if (dibujar["entities"][k] !== miJsonTemp["entities"][i]["entity"]["id"]) {
                        document.getElementById("entRel").innerHTML +=
                            '<div class="custom-control custom-checkbox">' +
                            '<input type="checkbox" class="custom-control-input" id="' + mId + '">' +
                            '<label class="custom-control-label" for="' + mId + '">' + miJsonTemp["entities"][i]["entity"]["name"] + '</label>' +
                            '<br/>' +
                            '</div>';
                    }
                    else  {
                        console.log((dibujar["entities"][k]));
                        document.getElementById("entRel").innerHTML +=
                            '<div class="custom-control custom-checkbox">' +
                            '<input type="checkbox" class="custom-control-input" id="' + mId + '" checked>' +
                            '<label class="custom-control-label" for="' + mId + '">' + miJsonTemp["entities"][i]["entity"]["name"] + '</label>' +
                            '<br/>' +
                            '</div>';
                        console.log(k);
                        k++;
                    }

                    $('#'+mId).click(function (){
                        console.log("Has pulsado "+mId);
                    });
                }
                ficha.innerHTML += '</div>';
                entitiesRetrieved = true;
            }
        }
        let myId = dibujar["id"];
        ficha.innerHTML += '<div class="text-center"><button type="button" class="btn btn-info" id="relBut">Confirmar</button></div>';


        $("#ficha").ready(function() {

            let originalEntity = entitiesRelatedArray;
            let originalProduct = productsRelatedArray;
            let originalPerson = personsRelatedArray;

            $(".custom-control-input").change(function () {
                console.log("Has pulsado " + this.id);
                console.log(tipo);
                console.log(dibujar);
                let encontrado = false;

                //Si es una entidad
                if(this.id.substr(0,3)==="ent"){
                    entitiesRelatedArray = dibujar.entities;
                    console.log(entitiesRelatedArray);
                    //sacamos el id del elemento pulsado
                    let elemento = this.id.substr(3,1);
                    elemento = parseInt(elemento);
                    console.log(elemento);
                    console.log(entitiesRelatedArray);
                    //Esto recorre el array SOLO SI EL ELEMENTO EXISTE: Hay que hacer el push fuera de aqui
                    for(let x=0; x<entitiesRelatedArray.length; x++){
                        console.log(entitiesRelatedArray[x]);
                        //Si el elemento ya existia marcado
                        if(entitiesRelatedArray[x]===elemento) {
                            encontrado = true;
                            //cuando quitamos el check
                            if(this.checked !== true) {
                                entitiesRelatedArray.splice(x,1);
                            }
                        }
                    }
                    if(!encontrado)
                    {
                        dibujar.entities.push(elemento);
                        console.log(entitiesRelatedArray);
                        entitiesRelatedArray = dibujar.entities;
                        console.log(entitiesRelatedArray);
                    }
                    console.log(dibujar);
                }

                //Si es un producto
                if(this.id.substr(0,3)==="pro"){
                    productsRelatedArray = dibujar.products;
                    console.log(productsRelatedArray);
                    //sacamos el id del elemento pulsado
                    let elemento = this.id.substr(3,1);
                    elemento = parseInt(elemento);
                    console.log(elemento);
                    console.log(productsRelatedArray);
                    //Esto recorre el array SOLO SI EL ELEMENTO EXISTE: Hay que hacer el push fuera de aqui
                    for(let x=0; x<productsRelatedArray.length; x++){
                        console.log(productsRelatedArray[x]);
                        //Si el elemento ya existia marcado
                        if(productsRelatedArray[x]===elemento) {
                            encontrado = true;
                            //cuando quitamos el check
                            if(this.checked !== true) {
                                productsRelatedArray.splice(x,1);
                            }
                        }
                    }
                    if(!encontrado)
                    {
                        dibujar.products.push(elemento);
                        console.log(productsRelatedArray);
                        productsRelatedArray = dibujar.products;
                        console.log(productsRelatedArray);
                    }
                    console.log(dibujar);
                }

                //Si es una persona
                if(this.id.substr(0,3)==="per"){
                    personsRelatedArray = dibujar.persons;
                    console.log(personsRelatedArray);
                    //sacamos el id del elemento pulsado
                    let elemento = this.id.substr(3,1);
                    elemento = parseInt(elemento);
                    console.log(elemento);
                    console.log(personsRelatedArray);
                    //Esto recorre el array SOLO SI EL ELEMENTO EXISTE: Hay que hacer el push fuera de aqui
                    for(let x=0; x<personsRelatedArray.length; x++){
                        console.log(personsRelatedArray[x]);
                        //Si el elemento ya existia marcado
                        if(personsRelatedArray[x]===elemento) {
                            encontrado = true;
                            //cuando quitamos el check
                            if(this.checked !== true) {
                                personsRelatedArray.splice(x,1);
                            }
                        }
                    }
                    if(!encontrado)
                    {
                        dibujar.persons.push(elemento);
                        console.log(personsRelatedArray);
                        personsRelatedArray = dibujar.persons;
                        console.log(personsRelatedArray);
                    }
                    console.log(dibujar);
                }
            });

            $("#relBut").click(async function () {

                let comparaEntidades = $(originalEntity).not(entitiesRelatedArray).length === 0 && $(entitiesRelatedArray).not(originalEntity).length === 0;
                if(!comparaEntidades) {
                    actualizaElemento(originalEntity, entitiesRelatedArray, myId, "entities", tipo);
                    await cerrar();
                }

                let comparaProductos = $(originalProduct).not(productsRelatedArray).length === 0 && $(productsRelatedArray).not(originalProduct).length === 0;
                if (!comparaProductos) {
                    actualizaElemento(originalProduct, productsRelatedArray, myId, "products", tipo);
                    await cerrar();
                }

                let comparaPersonas = $(originalPerson).not(personsRelatedArray).length === 0 && $(personsRelatedArray).not(originalPerson).length === 0;
                if (!comparaPersonas) {
                    actualizaElemento(originalPerson, personsRelatedArray, myId, "persons", tipo);
                    await cerrar();
                }


            });

        });
    }

    function actualizaElemento(OriginalArray, NuevoArray, myId, tipoOrigen, tipoDestino) {
    console.log(myId);

        for(let i in OriginalArray){
            $.ajax({
                type: "PUT",
                url: 'api/v1/' + tipoDestino + '/' + myId + '/' + tipoOrigen + '/rem/' + OriginalArray[i],
                headers: {"Authorization": authHeader},
                dataType: 'json',
                success: function (data) {
                    console.log("vacio");
                }
            });
        }
        for(let i in NuevoArray){
            $.ajax({
                type: "PUT",
                url: 'api/v1/' + tipoDestino + '/' + myId + '/' + tipoOrigen + '/add/' + NuevoArray[i],
                headers: {"Authorization": authHeader},
                dataType: 'json',
                success: function (data) {
                    console.log("lleno");
                }
            });
        }
        retrieveEntities();
        retrieveProducts();
        retrievePersons();
    }

    async function createNewUser(){
        let vacio = {'id': -1};
        await createUser(vacio);
        console.log(vacio);
    }

    async function createUser(usuario) {
        //Deshabilitamos el boton «login» cuando abrimos la ficha y lo rehabilitamos al cerrarla
        if (usuario['id'] === -1) {
            document.getElementById("loginBoton").disabled = true;
        }
        $("#ficha").ready(function () {

            document.getElementById("cerrar").onclick = function () {
                if (usuario['id'] === -1) {
                    document.getElementById("loginBoton").disabled = false;
                }
                cerrar();
            };
            /*document.getElementById("noSendForm").onclick=function () {
                if(usuario['id']===-1) {
                    document.getElementById("loginBoton").disabled = false;
                }
            }*/
        })
        let bodyElement = document.getElementById("ficha");
        bodyElement.innerHTML = '';
        bodyElement.innerHTML +=
            '<button class="btn btn-danger" id="cerrar">X</button>' +

            '<form id="newUserForm">' +
            '<div id="newUserFormWrapper">' +
            '<div class="fieldElement" id="fieldName">';
            bodyElement = document.getElementById("fieldName");
                if (usuario['id'] === -1){
                    bodyElement.innerHTML += '<label for="username" class="fieldTag">Nombre:</label>' +
                        '<input type="text" name="username" id="name" class="field-style">';
                } else {
                    bodyElement.innerHTML += '<label for="username" class="fieldTag">Nombre:</label>' +
                        '<input type="text" name="username" id="username" class="field-style" value='+ usuario.username +'>';
                    }
                bodyElement = document.getElementById("newUserFormWrapper");
                bodyElement.innerHTML += '</div>' +
                '<div class="fieldElement" id="fieldMail">';
            bodyElement = document.getElementById("fieldMail");
                if (usuario['id'] === -1){
                    bodyElement.innerHTML += '<label for="email" class="fieldTag">Correo:</label>' +
                        '<input type="email" name="email" id="email" class="field-style">';
                } else {
                    bodyElement.innerHTML += '<label for="email" class="fieldTag">Correo:</label>' +
                        '<input type="email" name="email" id="email" class="field-style" value='+ usuario.email +'>';
                }
            bodyElement = document.getElementById("newUserFormWrapper");
                bodyElement.innerHTML +=
                            '</div>' +
                    '<div class="fieldElement" id="fieldPassword">' +
                        '<label for="password" class="fieldTag">New Password:</label>' +
                        '<input type="password" name="password" id="password" class="field-style">' +
                    '</div>' +
                    '<div class="fieldDates" id="fieldBirthday">';
            bodyElement = document.getElementById("fieldBirthday");
                if (usuario['id'] === -1) {
                    bodyElement.innerHTML += '<label for="birthday" class="fieldTag">Birthday:</label>' +
                        '<input type="date" name="birthday" id="birthday" class="field-style">';
                } else {
                    bodyElement.innerHTML += '<label for="birthday" class="fieldTag">Birthday:</label>' +
                        '<input type="date" name="birthday" id="birthday" class="field-style" value='+ usuario["birthday"]["date"] +'>';
        }
        bodyElement = document.getElementById("newUserFormWrapper");
        bodyElement.innerHTML +=
                    '</div>' +
                '</div>'  +
                '<div id="newUserButtonWrapper">' +
                    '<button class="btn-success" id="sendForm">Enviar</button>' +
                    //'<button class="btn-danger" id="noSendForm" onclick="cerrar();">Cancelar</button>' +
                '</div>'  +
            '</form>' ;
        //$('#ficha').css("background-color", "rgba(0,0,0,0.92)");
        let color = "rgba(0,0,0,0.92)";
        await abrir("350", color);

        $('#sendForm').click(function () {
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

            $('#newUserForm').submit(function (e) {
                e.preventDefault();
                let data = $(this).serializeFormJSON();
                    data["role"] = "reader";
                    data["standby"] = true;
                    if(data["birthday"]===null) {delete data["birthday"];}
                console.log(data);
                let dataOrdenado = {};
                dataOrdenado["username"] = data["username"];
                dataOrdenado["email"] = data["email"];
                dataOrdenado["password"] = data["password"];
                dataOrdenado["birthday"] = data["birthday"];
                dataOrdenado["role"] = data["role"];
                dataOrdenado["standby"] = data["standby"];
                console.log(dataOrdenado);
                if (usuario['id'] !== -1) {
                    if(usuario["username"]===dataOrdenado["username"]){delete dataOrdenado["username"];}
                    if(usuario["email"]===dataOrdenado["email"]){delete dataOrdenado["email"];}
                    delete dataOrdenado["role"];
                    delete dataOrdenado["standby"];
                    delete dataOrdenado["password"];
                    //delete dataOrdenado["birthday"];
                    console.log(dataOrdenado);
                    $.ajax({
                        type: 'PUT',
                        url: 'api/v1/users/' + usuario["id"],
                        data: dataOrdenado,
                        //dataType: 'json',
                        success: function(data) {
                            console.log("Great Success");
                        }
                    })

                }else
                altaUser(dataOrdenado);
            });
            if(usuario['id']===-1) {
                document.getElementById("loginBoton").disabled = false;
            }
            cerrar();

        });

    }

