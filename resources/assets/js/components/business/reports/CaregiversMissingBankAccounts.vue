<template>
    <b-card>
        <div class="table-responsive">
            <b-table 
            :items="items"
                show-empty
                :fields="fields"
            >
                <template slot="name" scope="row">
                    <a :href="`/business/caregivers/${row.item.id}`">{{ row.item.name }}</a>
                </template>
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
