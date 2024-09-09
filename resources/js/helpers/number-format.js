/**
 * Format a number according to predefined locale
 * @param {Number} number 
 * @returns String
 */
export default function (number) {
    return new Intl.NumberFormat('it-CH').format(number);
}