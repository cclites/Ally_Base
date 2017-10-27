<template>
    <b-card
        :header="exception.title"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-row>
            <b-col lg="12">
                <p><strong>Description</strong></p>
                <p>
                    {{ exception.description | nl2br }}
                </p>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12" v-if="acknowledger">
                <p>Acknowledged by {{ acknowledger.firstname }} {{ acknowledger.lastname }} at {{ time }}</p>
                <p><strong>Notes</strong></p>
                <p>{{ exception.notes }}</p>
                <b-button variant="secondary" :href="exception.reference_url">{{ referenceUrlTitle }}</b-button>
            </b-col>
            <b-col lg="12" v-else>
                <b-form-group label="Add Notes" label-for="notes">
                    <b-textarea
                        id="notes"
                        name="notes"
                        :rows="3"
                        v-model="form.notes"
                        >
                    </b-textarea>
                    <input-help :form="form" field="notes" text=""></input-help>
                    <b-button variant="info" @click="acknowledge()">Acknowledge Exception</b-button>
                    <b-button variant="secondary" :href="exception.reference_url">{{ referenceUrlTitle }}</b-button>
                </b-form-group>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: {
            'exception': {},
            'acknowledger': {},
        },
        data() {
            return {
                'form': new Form({
                    'notes': null,
                }),
            }
        },
        computed: {
            time() {
                return moment.utc(this.exception.created_at).local().format('L LT');
            },
            referenceUrlTitle() {
                if (this.exception.reference_type === 'App\\Shift') {
                    return 'Link to Shift';
                }
                return 'Reference Link';
            }
        },
        methods: {
            acknowledge() {
                this.form.post('/business/exceptions/' + this.exception.id + '/acknowledge');
            }
        }
    }
</script>