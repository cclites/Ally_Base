<template>
    <b-row>
        <b-col sm="6">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th colspan="2">Clock In EVV</th>
                </tr>
                </thead>
                <tbody v-if="shift.checked_in_latitude || shift.checked_in_longitude">
                    <tr>
                        <th>Geocode</th>
                        <td>{{ shift.checked_in_latitude.slice(0,8) }}, {{ shift.checked_in_longitude.slice(0,8) }}</td>
                    </tr>
                    <tr>
                        <th>Distance</th>
                        <td>{{ convertToMiles(shift.checked_in_distance) }} mi</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <shift-map :address="shift.address" :lat="shift.checked_in_latitude" :lng="shift.checked_in_longitude" />
                        </td>
                    </tr>
                </tbody>
                <tbody v-else-if="shift.checked_in_number">
                    <tr>
                        <th>Phone Number</th>
                        <td>{{ shift.checked_in_number }}</td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="2">No EVV data</td>
                    </tr>
                </tbody>
            </table>
        </b-col>
        <b-col sm="6">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th colspan="2">Clock Out EVV</th>
                </tr>
                </thead>
                <tbody v-if="shift.checked_out_latitude || shift.checked_out_longitude">  
                    <tr>
                        <th>Geocode</th>
                        <td>{{ shift.checked_out_latitude.slice(0,8) }}, {{ shift.checked_out_longitude.slice(0,8) }}</td>
                    </tr>
                    <tr>
                        <th>Distance</th>
                        <td>{{ convertToMiles(shift.checked_out_distance) }} mi</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <shift-map :address="shift.address" :lat="shift.checked_out_latitude" :lng="shift.checked_out_longitude" />
                        </td>
                    </tr>
                </tbody>
                <tbody v-else-if="shift.checked_out_number">
                    <tr>
                        <th>Phone Number</th>
                        <td>{{ shift.checked_out_number }}</td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="2">No EVV data</td>
                    </tr>
                </tbody>
            </table>
        </b-col>
    </b-row>
</template>

<script>
    import FormatsDistance from "../../mixins/FormatsDistance";

    export default {
        mixins: [FormatsDistance],

        props: {
            shift: {
                type: Object,
                default: () => { return {} },
            },
        },

        methods: {
        },
    }
</script>
