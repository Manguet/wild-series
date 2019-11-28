/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');
require('bootstrap');

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

// Functions
document.getElementById("entry").onclick = function () {
    document.getElementById("dleft").classList.toggle("openl");
    document.getElementById("dright").classList.toggle("openr");
    document.getElementById("entry").classList.toggle("disapear");
    document.getElementById("fulldoors").classList.toggle("byebye")
};


