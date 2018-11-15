function amountToCurrency(elem) {
    var amount = '';
    var coins = JSON.parse(elem.getAttribute('data-available-coins'));
    var total = elem.getAttribute('data-order-total');
    var elementToChange = elem.getAttribute('data-element-to-change');

    var currentCurrency = elem.options[elem.selectedIndex].value;

    if (!currentCurrency) {
        document.getElementById(elementToChange).innerHTML = total;
    }
    if (currentCurrency === 'BTC') {
        amount = (coins.USD.rate_btc * total);
    } else {
        amount = (coins.USD.rate_btc * total) / coins[currentCurrency].rate_btc;
    }
    amount = amount.toFixed(7);
    document.getElementById(elementToChange).innerHTML = amount + ' ' + currentCurrency;
}