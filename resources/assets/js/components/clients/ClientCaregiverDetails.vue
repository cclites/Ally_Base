<template>
    <div>
        <b-row>
            <b-col sm="7">
                <p>
                    <strong>{{ caregiver.name }}</strong>
                </p>
                <p v-if="address && address.address1">
                    {{ address.address1 }}<br />
                    <span v-if="address.address2">{{ address.address2 }}<br /></span>
                    {{ address.city }}, {{ address.state }} {{ address.zip }}
                </p>
                <p v-if="phone">
                    {{ phone }}
                </p>
            </b-col>
            <b-col sm="5">
                <user-avatar :src="caregiver.avatar" />
            </b-col>
        </b-row>
    </div>

</template>

<script>
    export default {
        name: "ClientCaregiverDetails",

        props: {
            caregiver: {
                required: true,
                type: Object,
            },
            address: {
                type: Object,
                default: () => ({}),
            },
            phone: {
                type: String,
                default: () => ({}),
            }
        }
    }
</script>
