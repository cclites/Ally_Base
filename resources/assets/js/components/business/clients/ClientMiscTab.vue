<template>
    <b-row>
        <b-col>
            <custom-field-form :user-id="client.id" user-role="client" :meta="client.meta" />
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: {
            client: {
                type: Object,
                required: true,
            }
        },
    }
</script>