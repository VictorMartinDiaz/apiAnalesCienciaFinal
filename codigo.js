
function cargar() {
    var datos = {
        usuarios :[
            {clave: 1, nombre: "x", password: "x", tipo: "escritor"},
            {clave: 2, nombre: "y", password: "y", tipo: "escritor"},
            {clave: 3, nombre: "z", password: "z", tipo: "escritor"},
            {clave: 4, nombre: "a", password: "a", tipo: "lector"},
        ]
    }
    window.localStorage.setItem("datos", JSON.stringify(datos));
}

function validacion(){
    var datos = JSON.parse(window.localStorage.getItem("datos"));
    var nombre = document.getElementById("nombre").value;
    var password = document.getElementById("password").value;
    var usuario = getUsuario(datos, nombre, password);
    if (usuario == null){
        nombre.value = "";
        password.value = "";
    } else {
        window.sessionStorage.setItem("usuarioRegistrado", JSON.stringify(usuario));
        var login = document.getElementById("login");
        if (usuario.tipo === "escritor" || usuario.tipo === "lector"){
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

    $('.logoCentral').removeClass("escondida");
    $('.logoCentral').addClass("logoCentral");
    $('.banda').removeClass("escondida");
    $('.banda').addClass("banda");

    $('.banda').animate({
        opacity: '0.7'
    });

}
