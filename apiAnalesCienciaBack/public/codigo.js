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
                    // TODO Hacer un formulario más cuco
                    // TODO Hacer funcionar el boton crear
                    bodyElement.innerHTML += '<a href="formulario.html" class="btn btn-primary" rel="pop-up" id="crear">Crear</a>';
                    $("a[rel='pop-up']").click(function () {
                        let caracteristicas = "height=550,width=1150,scrollTo,resizable=1,scrollbars=1,location=0";
                        window.open(this.href, 'Popup', caracteristicas);
                        return false;

                    });

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

async function localizarElemento(elemento, tipo, nombre) {
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
                if (dibujar['persons'][i]['person'].name === nombre) {
                    found = true;
                    dibujar = dibujar['persons'][i]['person'];
                }
                break;
            }
            case 'entities': {
                console.log(dibujar['entities'][i]['entity'].name);
                if (dibujar['entities'][i]['entity'].name === nombre) {
                    found = true;
                    dibujar = dibujar['entities'][i]['entity'];
                }
                break;
            }
            case 'products': {
                console.log(dibujar['products'][i]['product'].name);
                if (dibujar['products'][i]['product'].name === nombre) {
                    found = true;
                    dibujar = dibujar['products'][i]['product'];
                }
                break;
            }
        }
    }
    await pintarFicha(nombre, tipo, dibujar);
}

async function eliminar(associatedJson, tipo) {

    console.log(localStorage);
    localStorage.removeItem(associatedJson.name);
    document.getElementById(associatedJson.name).innerHTML='';

    deleteFromDB(associatedJson.id, tipo);

    existe = false;
    await cerrar();
}