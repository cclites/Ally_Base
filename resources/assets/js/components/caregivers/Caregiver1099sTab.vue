<template>
        <b-card header="Tax Documents"
                header-bg-variant="info"
                header-text-variant="white">

            <p>
                If you do not see a 1099 listed, your client did not elect provide you with one. This is common and okay.
                In that case, to report your income, please see your
                <a href="/caregiver/deposits">Year Summary Report on your Pay Statements Screen</a> and fill out a
                <a href="https://www.irs.gov/pub/irs-pdf/f1040sc.pdf" target="_blank">Schedule C</a> from the IRS.
            </p>

            <p>
                1099 totals do not include expenses, mileage, deductions, adjustments, etc.
            </p>

            <hr>

            <div v-if="fields.length">
                <div v-for="field in fields" :key="field" class="mb-3">
                    <h4 class="chat-text">{{ field }}</h4>
                    <b-row v-for="item in items[field]" :key="item.id">
                        <b-col md="4">
                            {{ item.name }}
                        </b-col>
                        <b-col md="4">
                            <a :href=" '/caregiver/caregiver-1099/download/' + item.id ">Download 1099</a>
                        </b-col>

                    </b-row>
                </div>
            </div>
            <div v-else>
                There are no records to display.
            </div>

            <hr>

            2018 and prior years would have been mailed to you and are not available electronically.

        </b-card>
</template>

<script>
    export default {
        name: "Caregiver1099sTab",

        props: {
            caregiver: ''
        },

        data() {
            return {
                items: [],
                fields: [],
            }
        },

        mounted(){
            this.load1099s();
        },

        methods:{
            load1099s(){
                axios.get('caregiver/caregiver-1099/' + this.caregiver)
                    .then(response => {
                        this.items = response.data;
                        this.fields = Object.keys(this.items);
                    })
                    .catch( e => {})
                    .finally(() => {});
            }
        }
    }
</script>

<style scoped>

</style>