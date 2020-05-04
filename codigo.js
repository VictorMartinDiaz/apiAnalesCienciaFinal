
function cargar() {
    var datos = {
        usuarios :[
            {clave: 1, nombre: "x", contraseña: "x", tipo: "escritor"}, 
            {clave: 2, nombre: "y", contraseña: "y", tipo: "escritor"}, 
            {clave: 3, nombre: "z", contraseña: "z", tipo: "escritor"}, 
            {clave: 4, nombre: "a", contraseña: "a", tipo: "lector"}, 
        ]
    }
    window.localStorage.setItem("datos", JSON.stringify(datos));
}

function validacion(){
    var datos = JSON.parse(window.localStorage.getItem("datos"));
    var nombre = document.getElementById("nombre").value;
    var contraseña = document.getElementById("contraseña").value;
    var usuario = getUsuario(datos, nombre, contraseña);
    if (usuario == null){
        nombre.value = "";
        contraseña.value = "";
    } else {
        var tipo = window.sessionStorage.setItem("usuarioRegistrado", JSON.stringify(usuario));
        var login = document.getElementById("login");
        if (usuario.tipo == "escritor" || usuario.tipo == "lector"){
            login.action = "./main.html";
        } 
    }
    return usuario != null;
}

function getUsuario(datos, nombre, contraseña){
    for(usuario of datos.usuarios){
        if (usuario.nombre == nombre && usuario.contraseña == contraseña){
            return usuario;
        }
    }
    return null;
}

function sacaFicha(){
    $("#ficha").animate({
    opacity: '1',
    height: '550px',
    width: '550px',
    });
    $('.banda').animate({
        opacity: '0'
    });}
