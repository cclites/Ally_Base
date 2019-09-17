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

            /**
             * Claims
             */
            CLAIM_STATUSES: {
                NOT_SENT: 'NOT_SENT',
                CREATED: 'CREATED',
                TRANSMITTED: 'TRANSMITTED',
                RETRANSMITTED: 'RETRANSMITTED',
                ACCEPTED: 'ACCEPTED',
                REJECTED: 'REJECTED',
            },
            claimStatusOptions: [
                { value: 'NOT_SENT', text: 'Not Sent' },
                { value: 'CREATED', text: 'Created' },
                { value: 'TRANSMITTED', text: 'Transmitted' },
                { value: 'RETRANSMITTED', text: 'Re-Transmitted' },
                { value: 'ACCEPTED', text: 'Accepted' },
                { value: 'REJECTED', text: 'Rejected' },
            ],

            CLAIMABLE_TYPES: {
                EXPENSE: 'App\\Claims\\ClaimableExpense',
                SERVICE: 'App\\Claims\\ClaimableService',
            },
            CLAIM_REMIT_TYPES: {
                TAKE_BACK: 'take-back',
                REMIT: 'remit',
            },
            claimRemitTypeOptions: [
                { value: 'remit', text: 'Remit' },
                { value: 'take-back', text: 'Take Back' },
            ],
            CLAIM_REMIT_STATUS: {
                NOT_APPLIED: 'not_applied',                
                PARTIALLY_APPLIED: 'partially_applied',                
                FULLY_APPLIED: 'fully_applied',                
            },
            claimRemitStatusOptions: [
                { value: 'not_applied', text: 'Not Applied' },
                { value: 'partially_applied', text: 'Partially Applied' },
                { value: 'fully_applied', text: 'Fully Applied' },
            ],
            CLAIM_ADJUSTMENT_TYPES: {
                DENIAL: 'denial',
                DISCOUNT: 'discount',
                INTEREST: 'interest',
                OVERPAYMENT: 'overpayment',
                PARTIAL: 'partial',
                PAYMENT: 'payment',
                SUPPLIED_CONTRIBUTION: 'supplier-contribution',
                TAKE_BACK: 'take-back',
                WRITE_OFF: 'write-off',
            },
            claimAdjustmentTypeOptions: [
                { value: 'denial', text: 'Denial' },
                { value: 'discount', text: 'Discount' },
                { value: 'interest', text: 'Interest' },
                { value: 'overpayment', text: 'Overpayment / Surplus' },
                { value: 'partial', text: 'Partial Payment Applied' },
                { value: 'payment', text: 'Payment Applied' },
                { value: 'supplier-contribution', text: 'Supplier Contribution' },
                { value: 'take-back', text: 'Take Back' },
                { value: 'write-off', text: 'Write Off / Uncollectible' },
            ],
        }
    },
}
