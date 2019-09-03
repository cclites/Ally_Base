export default {
    data() {
        return {
            PRIVATE_PAY_ID: 0,
            OFFLINE_PAY_ID: 1,

            CLAIM_SERVICE: {
                HHA: 'HHA',
                TELLUS: 'TELLUS',
                CLEARINGHOUSE: 'CLEARINGHOUSE',
                EMAIL: 'EMAIL',
                FAX: 'FAX',
            },

            ethnicityOptions: [
                { value: 'american_indian', text: 'American Indian or Alaska Native' },
                { value: 'asian', text: 'Asian' },
                { value: 'black', text: 'Black or African American' },
                { value: 'hispanic', text: 'Hispanic or Latino' },
                { value: 'hawaiian', text: 'Native Hawaiian or Other Pacific Islander' },
                { value: 'white', text: 'White or Caucasian' },
            ],
            smsLength: 160,
            clientTypes: [
                {value:'', text: "All Client Types"},
                {value:'private_pay', text:'Private Pay'},
                {value:'medicaid', text:'Medicaid'},
                {value:'LTCI', text:'LTCI'},
                {value:'VA', text:'VA'},
                {value:'lead_agency', text:'Lead Agency'},
            ],

            EDI_CODE_GUIDE_URL: 'https://s3.amazonaws.com/hhaxsupport/SupportDocs/EDI+Guides/EDI+Code+Table+Guides/EDI+Code+Table+Guide_Florida.pdf',
            SHIFT_MAX_FUTURE_END_DATE: 168, // hours

            CLAIMABLE_TYPES: {
                EXPENSE: 'App\\ClaimableExpense',
                Service: 'App\\ClaimableService',
            }
        }
    },
}
