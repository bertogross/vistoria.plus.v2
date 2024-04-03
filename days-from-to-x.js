function addDaysToDate(dateString, daysToAdd) {
    const date = new Date(dateString);
    date.setDate(date.getDate() + daysToAdd);
    return date;
}

const initialDate = "2024-02-29";
const daysToAdd = 175;

const resultDate = addDaysToDate(initialDate, daysToAdd);

console.log(`Data inicial: ${initialDate}`);
console.log(`Adicionando ${daysToAdd} dias, obtemos: ${resultDate.toISOString().split('T')[0]}`);

// run on terminal:
    // node days-from-to-x.js
