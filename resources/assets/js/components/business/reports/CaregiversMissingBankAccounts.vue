<template>
    <b-card>
        <div class="table-responsive">
            <b-table :items="items"
                     show-empty
                     :fields="fields">
            </b-table>
        </div>
    </b-card>
</template>

<script>
    export default {
        props: ['caregivers'],
        
        data() {
            return {
                fields: [
                    'name',
                    'email'
                ]
            }
        },
        
        computed: {
            items() {
                return _.map(this.caregivers, (caregiver) => {
                    if (caregiver.shifts.length > 0) {
                        caregiver._rowVariant = 'danger';
                    }
                    return caregiver;
                });
            }
        }
    }
</script>
