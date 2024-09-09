/**
 * Format a number according to predefined locale as a price
 * @param {Number} number 
 * @returns String
 */
export default function (number) {
    return new Intl.NumberFormat('it-CH', { style: "currency", currency: "CHF"}).format(number);
}