let attributes = document.getElementById("product_attributes");
let toolbar = attributes.getElementsByClassName("toolbar");


let form = document.createElement("form");
form.type = "form";
form.method = "POST";
form.name = "csv_form";
form.action = "";
form.className = "form";
form.style.marginTop = "10px";
form.enctype = "multipart/form-data";
toolbar[0].appendChild(form);


let button = document.createElement("input");
button.type = "submit";
button.name = "submit_button";
button.className = "button import_csv";
button.value = "Import";
button.style.marginLeft = "30px";

let file = document.createElement("input");
file.type = "file";
file.className = "csv_file";
file.id = 'csv_name';
file.innerHTML = "Choose File";
file.style.marginLeft = "5px";
file.name = "csv_name";

let the_form = toolbar[0].getElementsByClassName("form");
toolbar[0].appendChild(form);
the_form[0].appendChild(button);
the_form[0].appendChild(file);