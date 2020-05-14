function slideToggleAutores() {
    $('#autores').slideToggle();
    $('#entidades').slideUp();
    $('#productos').slideUp();
}

function slideToggleEntidades() {
    $('#entidades').slideToggle();
    $('#autores').slideUp();
    $('#productos').slideUp();
}

function slideToggleProductos() {
    $('#productos').slideToggle();
    $('#autores').slideUp();
    $('#entidades').slideUp();
}

async function cerrar() {
    $("#ficha").animate({
        opacity: '0',
        height: '0px',
        width: '0px',
    });
    $('.banda').animate({
        opacity: '0'
    });
    await new Promise(r => setTimeout(r, 350));
    $('.logoCentral').removeClass("escondida");
    $('.banda').removeClass("escondida");
    $('.banda').animate({
        opacity: '0.87'
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
            }
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
            const usuarioRegistrado = localStorage.getItem('usuarioRegistrado');

            if (usuarioRegistrado === "reader" || usuarioRegistrado === "writer") {
                let bodyElement = "";

                $("#ficha").click(function () {
                    document.getElementById("logout").style.visibility = "visible";
                });

                bodyElement = document.getElementById("botonera");
                bodyElement.innerHTML = "<button class='btn btn-danger' id='logout'>Logout</button>";

                if (usuarioRegistrado === "writer") {
                    // TODO Hacer un formulario más cuco
                    // TODO Hacer funcionar el boton crear
                    bodyElement.innerHTML += '<a href="formulario.html" class="btn btn-primary" rel="pop-up" id="crear">Crear</a>';
                    $("a[rel='pop-up']").click(function () {
                        let caracteristicas = "height=550,width=1150,scrollTo,resizable=1,scrollbars=1,location=0";
                        window.open(this.href, 'Popup', caracteristicas);
                        return false;

                    });
                    /*let blurred = false;
                    window.onblur = function () {
                        blurred = true;
                    };
                    window.onfocus = function () {
                        blurred && (location.reload());
                    };*/
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

function retrieveProducts() {
    let productosElement = document.getElementById("productos");

    $.ajax({
        type: 'GET',
        url: 'api/v1/products',
        headers: {"Authorization": authHeader},
        dataType: 'json',
        success: function (data) {
            data['products'].forEach(i => {
                productosElement.innerHTML +=
                    '<a class="dropdown-item" href="" id="' + i['product'].name + '">' +
                    '<img class="dropdownImg" src=' + i['product'].imageUrl+ ' />' +
                    '<span class="name id="' + i['product'].name + '">' + i['product'].name +
                    '</span>' +
                    '</a>'
            });
            /*console.log(data['products']);
            console.log(data['products'][1]);
            console.log(data['products'][1]['product']);
            console.log(data['products'][1]['product'].name);*/
            //$('#info').html(JSON.stringify(data));


        },
    })
}

function retrievePersons() {
    let personasElement = document.getElementById("autores");

    $.ajax({
        type: 'GET',
        url: 'api/v1/persons',
        headers: {"Authorization": authHeader},
        dataType: 'json',
        success: function (data) {
            data['persons'].forEach(i => {
                personasElement.innerHTML +=
                    '<a class="dropdown-item" href="" id="' + i['person'].name + '">' +
                    '<img class="dropdownImg" src=' + i['person'].imageUrl+ ' />' +
                    '<span class="name id="' + i['person'].name + '">' + i['person'].name +
                    '</span>' +
                    '</a>'
            });
        },
    })
}

function retrieveEntities() {
    let entidadesElement = document.getElementById("entidades");

    $.ajax({
        type: 'GET',
        url: 'api/v1/entities',
        headers: {"Authorization": authHeader},
        dataType: 'json',
        success: function (data) {
            data['entities'].forEach(i => {
                entidadesElement.innerHTML +=
                    '<a class="dropdown-item" href="" id="' + i['entity'].name + '">' +
                    '<img class="dropdownImg" src=' + i['entity'].imageUrl+ ' />' +
                    '<span class="name id="' + i['entity'].name + '">' + i['entity'].name +
                    '</span>' +
                    '</a>'
            });
        },
    })
}