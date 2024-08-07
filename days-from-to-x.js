function addDaysToDate(dateString, daysToAdd) {
    const date = new Date(dateString);
    date.setDate(date.getDate() + daysToAdd);
    return date;
}

const initialDate = "2024-03-05"; // Y-m-d
const daysToAdd = 113;

const resultDate = addDaysToDate(initialDate, daysToAdd);

console.log(`Data inicial: ${initialDate}`);
console.log(`Adicionando ${daysToAdd} dias, obtemos: ${resultDate.toISOString().split('T')[0]}`);

// run on terminal:
    // node days-from-to-x.js
