
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
        var clase = window.sessionStorage.setItem("usuarioRegistrado", JSON.stringify(usuario));
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

/*function asyncFicha(paso, mostrado, mostrar) {
    switch (paso) {
        case 0: {
            mostrado = false;
            $("#ficha").animate({
                opacity: '0',
                height: '0px',
                width: '0px',
            });
            paso++;
            asyncFicha(paso, mostrado);
            break;
        }
        case 1: {
            $("#ficha").hide();
            paso++;
            asyncFicha(paso, mostrado);
            break;
        }
        case 2: {
            document.getElementById("ficha").innerHTML = "";
            paso++;
            asyncFicha(paso, mostrado);
            break;
        }
        case 3: {
            for(let i=0; i<=localStorage.length-1; i++) {

                //console.log(localStorage.getItem(i));

                if(localStorage.getItem(i)===mostrar &&! mostrado){
                    mostrado = true;
                    var miNombre0 = window.localStorage.getItem(i);
                    console.log(miNombre0);
                    var recibir0 = window.localStorage.getItem(miNombre0);
                    console.log(recibir0);
                    var dibujar0 = JSON.parse(recibir0);
                    console.log(dibujar0);
                    //  console.log(i.nombre);
                    let template0 = [{'<>':'img','src':'${foto}','class':'laImg'}, {'<>':'h1','html':'${nombre}'}, {'<>':'p','html':'Fecha de nacimiento: ${fechaNacimiento}'}, {'<>':'p','html':'Fecha de defunción: ${fechaDefuncion}'}, {'<>':'a', 'href':'${wiki}','html':'wikipedia'}];


                    let data0 = [dibujar0];
                    document.getElementById("ficha").innerHTML += ( json2html.transform(data0,template0) );


                    let bodyElement = document.getElementById("ficha");
                    bodyElement.innerHTML += '<br/>';
                    if(usuarioRegistrado.tipo == "escritor"){bodyElement.innerHTML += '<a href="formulario.html" class="btn btn-info" rel="pop-up" id="editar">Editar</a>';}
                    if(usuarioRegistrado.tipo == "escritor"){bodyElement.innerHTML += '<button class="btn btn-warning" rel="pop-up" id="eliminar">Eliminar</button>';}
                    bodyElement.innerHTML += '<button class="btn btn-danger" rel="pop-up" id="cerrar">X</button>';

                } // Cierro el if

            }//cierro el for/!**!/
            paso++;
            asyncFicha(paso, mostrado);
            break;
        }
        case 4: {
            $("#ficha").show();
            paso++;
            asyncFicha(paso, mostrado);
            break;
        }
        case 5: {
            $("#ficha").animate({
                opacity: '1',
                height: '550px',
                width: '550px',
            });
            $('.banda').animate({
                opacity: '0'
            });
            break;
        }
    }
}*/

function slideToggleAutores(){
    $('#autores').slideToggle();
    $('#entidades').slideUp();
    $('#productos').slideUp();
};

function slideToggleEntidades(){
    $('#entidades').slideToggle();
    $('#autores').slideUp();
    $('#productos').slideUp();
};

function slideToggleProductos(){
    $('#productos').slideToggle();
    $('#autores').slideUp();
    $('#entidades').slideUp();
};

