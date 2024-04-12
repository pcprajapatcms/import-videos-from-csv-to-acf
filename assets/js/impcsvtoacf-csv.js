"use strict";

document.addEventListener("DOMContentLoaded", function () {
  const repeaterDropdown = document.getElementById("repeater-fields");
  const subfieldsDropdown = document.getElementById("subfields");
  const sub_field_name = document.getElementById("sub_field_name");

  function loadSubfields() {
    const selectedOption =
      repeaterDropdown.options[repeaterDropdown.selectedIndex];
    const subfieldsData = JSON.parse(
      selectedOption.getAttribute("data-subfields")
    );
    subfieldsDropdown.innerHTML = '<option value="">Select Subfield</option>';
    if (subfieldsData && subfieldsData.length > 0) {
      subfieldsData.forEach(function (subfield) {
        const option = document.createElement("option");
        option.value = subfield.name;
        option.textContent = subfield.label;
        if (sub_field_name.value === subfield.name) {
          option.setAttribute("selected", "selected");
        }
        subfieldsDropdown.appendChild(option);
      });
      subfieldsDropdown.disabled = false;
    } else {
      subfieldsDropdown.disabled = true;
    }
  }

  repeaterDropdown.addEventListener("change", function () {
    loadSubfields();
  });
  loadSubfields();

  subfieldsDropdown.addEventListener("change", function () {
    const selectedOption =
      subfieldsDropdown.options[subfieldsDropdown.selectedIndex].value;
    sub_field_name.value = selectedOption;
  });
});
