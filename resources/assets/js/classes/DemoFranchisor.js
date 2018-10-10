export default class DemoFranchisor {

    static getFranchises() {
        return [
            { name: 'Griswold Broward', address: '123 Main St.', phone_number: '(555) 555-5553', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold Ft Myers', address: '123 Main St.', phone_number: '(555) 555-5553', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold Jacksonville', address: '123 Main St.', phone_number: '(555) 555-5553', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold Mercer County', address: '123 Main St.', phone_number: '(555) 555-5553', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold Miami', address: '123 Main St.', phone_number: '(555) 555-5552', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold Ohio', address: '123 Main St.', phone_number: '(555) 555-5553', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold Phoenix', address: '123 Main St.', phone_number: '(555) 555-5553', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold Plano', address: '123 Main St.', phone_number: '(555) 555-5551', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold San Francisco', address: '123 Main St.', phone_number: '(555) 555-5553', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold Sarasota', address: '123 Main St.', phone_number: '(555) 555-5553', owner: 'Demo Franchisor', last_active: '10/10/2018'},
            { name: 'Griswold Seattle', address: '123 Main St.', phone_number: '(555) 555-5553', owner: 'Demo Franchisor', last_active: '10/10/2018'},
        ];
    }

    static getPayments() {
        let payments = [];
        let dates = [moment().subtract(1, 'days').format('MM/DD/YYYY'), moment().subtract(8, 'days').format('MM/DD/YYYY')];
        for (let date of dates) {
            for (let franchise of this.getFranchises()) {
                let amount = Math.random() * 25000;
                payments.push({
                    date: date,
                    office: franchise.name,
                    total_amount: amount.toFixed(2),
                    royalty_owed: (amount * .12).toFixed(2),
                });
            }
        }
        return payments;
    }
}
