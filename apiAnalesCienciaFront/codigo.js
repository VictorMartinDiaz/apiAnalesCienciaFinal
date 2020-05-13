
function cargar() {
    let datos = {
        usuarios :[
            {clave: 1, nombre: "x", password: "x", scope: "reader+writer"},
            {clave: 2, nombre: "y", password: "y", scope: "reader+writer"},
            {clave: 3, nombre: "z", password: "z", scope: "reader+writer"},
            {clave: 4, nombre: "a", password: "a", scope: "reader"},
            {clave: 5, nombre: "Victor", password: "analesCiencia20", scope: "reader+writer"},
        ]
    }
    window.localStorage.setItem("datos", JSON.stringify(datos));

}

function validacion(){
    let datos = JSON.parse(window.localStorage.getItem("datos"));
    let nombre = document.getElementById("nombre").value;
    let password = document.getElementById("password").value;
    let usuario = getUsuario(datos, nombre, password);
    if (usuario == null){
        nombre.value = "";
        password.value = "";
    } else {
        window.sessionStorage.setItem("usuarioRegistrado", JSON.stringify(usuario));
        let login = document.getElementById("login");
        if (usuario.scope === "reader+writer" || usuario.scope === "reader"){
            login.action = "./main.html";
        }
    }
    return usuario != null;
}

function getUsuario(datos, nombre, password){
    let usuario;
    for(usuario of datos.usuarios){
        if (usuario.nombre === nombre && usuario.password === password){

            return usuario;
        }
    }
    return null;
}



function slideToggleAutores(){
    $('#autores').slideToggle();
    $('#entidades').slideUp();
    $('#productos').slideUp();
}

function slideToggleEntidades(){
    $('#entidades').slideToggle();
    $('#autores').slideUp();
    $('#productos').slideUp();
}

function slideToggleProductos(){
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

