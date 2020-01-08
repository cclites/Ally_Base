<template>
    <div>
        <b-row>
            <b-col lg="6">
                <b-card
                        header="Client Charges for Date Range &amp; Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <div id="client-charge-summary">
                        <h5 class="d-none d-print-block">Client Charge Summary</h5>
                        <div class="pull-right hidden-print">
                            <button type="button" class="btn btn-default" @click="printClientSummary()" style="margin-top: -110px">Print</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Hours</th>
                                    <th v-if="isAdmin">Pre Ally</th>
                                    <th>Total</th>
                                    <!--<th>Caregiver</th>-->
                                    <!--<th>Registry</th>-->
                                    <!--<th>Ally</th>-->
                                    <th>Type</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="item in clientCharges" :key="item.id">
                                    <td><a :href="'/business/clients/' + item.id">{{ item.name }}</a></td>
                                    <td>{{ item.hours }}</td>
                                    <td v-if="isAdmin">{{ moneyFormat(preAllyTotal(item)) }}</td>
                                    <td>{{ moneyFormat(item.total) }}</td>
                                    <!--<td>{{ item.caregiver_total }}</td>-->
                                    <!--<td>{{ item.provider_total }}</td>-->
                                    <!--<td>{{ item.ally_total }}</td>-->
                                    <td>{{ item.payment_type }}</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td><strong>Total for Confirmed Shifts</strong></td>
                                    <td>{{ clientTotals.hours }}</td>
                                    <td v-if="admin">{{ moneyFormat(preAllyTotal(clientTotals)) }}</td>
                                    <td>{{ moneyFormat(clientTotals.total) }}</td>
                                    <td></td>
                                    <!--<td>{{ clientTotals.caregiver_total }}</td>-->
                                    <!--<td>{{ clientTotals.provider_total }}</td>-->
                                    <!--<td>{{ clientTotals.ally_total }}</td>-->
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card
                        header="Caregiver Payments for Date Range &amp; Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <div id="caregiver-payment-summary">
                        <h4 class="d-none d-print-block">Caregiver Payment Summary</h4>
                        <div class="pull-right hidden-print">
                            <button type="button" class="btn btn-default" @click="printCaregiverSummary()" style="margin-top: -110px">Print</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Caregiver</th>
                                    <th>Hours</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="item in caregiverPayments" :key="item.id">
                                    <td><a :href="'/business/caregivers/' + item.id">{{ item.name }}</a></td>
                                    <td>{{ item.hours }}</td>
                                    <td>{{ moneyFormat(item.amount) }}</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td><strong>Total for Confirmed Shifts</strong></td>
                                    <td>{{ caregiverTotals.hours }}</td>
                                    <td>{{ moneyFormat(caregiverTotals.amount) }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="6">
                <b-card>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Provider Payment For Date Range &amp; Filters:</strong></td>
                            <td>{{ moneyFormat(clientTotals.provider_total) }}</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
            <b-col lg="6" v-if=" admin ">
                <b-card>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Processing Fee For Date Range &amp; Filters:</strong></td>
                            <td>{{ moneyFormat(clientTotals.ally_total) }}</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import AuthUser from '../../mixins/AuthUser';

    export default {
        mixins: [FormatsNumbers, AuthUser],

        props: {
            clientCharges: Array,
            caregiverPayments: Array,
            admin: Number,
        },

        computed: {
            clientTotals() {
                if (this.clientCharges.length === 0) return {};
                return this.clientCharges.reduce((totals, item) => {
                    return {
                        hours: (this.parseFloat(totals.hours) + this.parseFloat(item.hours)).toFixed(2),
                        total: (this.parseFloat(totals.total) + this.parseFloat(item.total)).toFixed(2),
                        caregiver_total: (this.parseFloat(totals.caregiver_total) + this.parseFloat(item.caregiver_total)).toFixed(2),
                        provider_total: (this.parseFloat(totals.provider_total) + this.parseFloat(item.provider_total)).toFixed(2),
                        ally_total: (this.parseFloat(totals.ally_total) + this.parseFloat(item.ally_total)).toFixed(2),
                    }
                })
            },
            caregiverTotals() {
                if (this.caregiverPayments.length === 0) return {};
                return this.caregiverPayments.reduce((totals, item) => {
                    return {
                        amount: (this.parseFloat(totals.amount) + this.parseFloat(item.amount)).toFixed(2),
                        hours: (this.parseFloat(totals.hours) + this.parseFloat(item.hours)).toFixed(2),
                    }
                })
            },
        },

        methods: {
            parseFloat(float) {
                if (typeof(float) === 'string') {
                    float = float.replace(',', '');
                }
                return parseFloat(float);
            },
            preAllyTotal(item) {
                return this.parseFloat(item.provider_total) + this.parseFloat(item.caregiver_total);
            },
            printClientSummary() {
                $('#client-charge-summary').print();
            },
            printCaregiverSummary() {
                $('#caregiver-payment-summary').print();
            }
        }
    }
</script>
