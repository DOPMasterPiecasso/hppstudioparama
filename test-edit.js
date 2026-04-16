const { JSDOM } = require("jsdom");
const dom = new JSDOM(`
  <div class="addon-item">
     <div class="addon-price-display">100</div>
  </div>
`);
const document = dom.window.document;
const priceElement = document.querySelector('.addon-price-display');
const input = document.createElement('input');
input.value = "200";

// editAddonPrice
priceElement.replaceWith(input);

// onblur
const newPrice = parseInt(input.value || 0);
priceElement.textContent = newPrice;
// NOT replacing it back!

// collect
const item = document.querySelector('.addon-item');
const priceEls = item.querySelectorAll('.addon-price-display');
console.log("Found:", priceEls.length);
