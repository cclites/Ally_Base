<template>
    <b-card>
        <b-table :items="caregivers"
                 :fields="fields">
        </b-table>
    </b-card>
</template>

<script>
    export default {
        props: ['caregivers'],
        
        data() {
            return {
                fields: [
                    {
                        key: 'name'
                    }
                ]
            }
        },
        
        methods: {
        
        },
        
        computed: {
        
        }
    }
</script>