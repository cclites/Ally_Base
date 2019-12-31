<template>
    <div>
        <b-row v-for="section in sections" :key="section.name">
            <b-col>
                <h4>{{ section.name }}</h4>

                <p v-for="field in section.fields" :key="field.key">

                    <span class="title">{{ field.title ? field.title : uppercaseWords(field.key) + ':' }} </span>
                    <span class="answer" v-if="field.formatter">
                        {{ field.formatter(answer(field.key)) }}
                    </span>
                    <span class="answer" v-if="Array.isArray(answer(field.key))">
                        {{ answer(field.key).map(item => uppercaseWords(item)).join(', ') }}
                    </span>
                    <span class="answer" v-else-if="answer(field.key) === 0">
                        <span class="badge badge-danger"><i class="fa fa-times"></i> No</span><br />
                    </span>
                    <span class="answer" v-else-if="answer(field.key) === 1">
                        <span class="badge badge-success"><i class="fa fa-check"></i> Yes</span><br />
                    </span>
                    <span class="answer" v-else-if="answer(field.key)">
                        {{ uppercaseWords(answer(field.key).toString()) }}
                    </span>
                    <span class="description" v-for="(description, title) in field.descriptions" v-if="description">
                        <span class="title">{{ title }}</span><br />
                        <span class="answer">{{ answer(description) }}</span>
                        <br />
                    </span>
                </p>
            </b-col>
        </b-row>
        <b-row v-if="careDetails.comments">
            <b-col>
                <h4>Comments</h4>
                <p class="answer">{{ careDetails.comments }}</p>
            </b-col>
        </b-row>
        <b-row v-if="careDetails.instructions">
            <b-col>
                <h4>Special Instructions</h4>
                <p class="answer">{{ careDetails.instructions }}</p>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import FormatsStrings from "../mixins/FormatsStrings";

    export default {
        name: "CareDetailsDisplay",

        mixins: [FormatsStrings],

        props: {
            careDetails: {
                required: true,
                type: Object,
            },
        },

        data() {
            return {
                sections: [
                    {
                        name: 'General',
                        fields: [
                            {
                              key: 'height',
                            },
                            {
                                key: 'weight',
                            },
                            {
                                key: 'lives_alone',
                            },
                            {
                                key: 'pets',
                            },
                            {
                                key: 'smoker',
                            },
                            {
                                key: 'alcohol',
                            },
                            {
                                key: 'incompetent',
                                title: 'Deemed Incompetent:',
                            },
                        ]
                    },
                    {
                        name: 'Care Details',
                        fields: [
                            {
                                key: 'can_provide_direction',
                                title: 'Consumer is able to provide direction to the caregiver when taking medication:'
                            },
                            {
                                key: 'assist_medications',
                                title: 'Assist with Medications:',
                                descriptions: {
                                    'Medication Responsibilities': 'medication_overseer',
                                },
                            },
                            {
                                key: 'safety_measures',
                                descriptions: {
                                    'Instructions': 'safety_instructions',
                                },
                            },
                            {
                                key: 'mobility',
                                descriptions: {
                                    'Instructions': 'mobility_instructions',
                                },
                            },
                            {
                                key: 'toileting',
                                descriptions: {
                                    'Instructions': 'toileting_instructions',
                                },
                            },
                            {
                                key: 'bathing',
                                descriptions: {
                                    'Frequency': 'bathing_frequency',
                                    'Instructions': 'bathing_instructions',
                                },
                            },
                            {
                                key: 'vision',
                            },
                            {
                                key: 'hearing',
                                descriptions: {
                                    'Instructions': 'hearing_instructions',
                                },
                            },
                            {
                                key: 'diet',
                                descriptions: {
                                    'Likes': 'diet_likes',
                                    'Feeding Instructions': 'feeding_instructions',
                                },
                            },
                            {
                                key: 'skin',
                                title: 'Skin Care',
                                descriptions: {
                                    'Skin Conditions': 'skin_conditions',
                                },
                            },
                            {
                                key: 'hair',
                                title: 'Hair Care',
                                descriptions: {
                                    'Hair Care Frequency': 'hair_frequency',
                                },
                            },
                            {
                                key: 'oral',
                                title: 'Oral Care'
                            },
                            {
                                key: 'shaving',
                                descriptions: {
                                    'Instructions': 'shaving_instructions',
                                },
                            },
                            {
                                key: 'nails',
                                title: 'Nail Care'
                            },
                            {
                                key: 'dressing',
                                descriptions: {
                                    'Instructions': 'dressing_instructions',
                                },
                            },
                            {
                                key: 'housekeeping',
                                descriptions: {
                                    'Instructions': 'housekeeping_instructions',
                                },
                            },
                            {
                                key: 'errands',
                            },
                            {
                                key: 'supplies',
                                descriptions: {
                                    'Instructions': 'supplies_instructions',
                                },
                            },
                        ]
                    },
                ]
            }
        },

        methods: {
            answerExists(key) {
                let answer = this.answer(key);
                if (answer === null || answer === undefined) return false;
                return answer.toString().trim().length > 0;
            },
            answer(key) {
                return this.careDetails[key];
            },

        }
    }
</script>

<style scoped>
    h4 {
        text-decoration: underline;
    }
    .badge-success {
        background-color: forestgreen;
    }
    .title {
        color: #000;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .answer {
        color: #444;
        white-space: pre-line;
    }
    .description .title {
        text-transform: uppercase;
        font-size: 12px;
        color: #007bff;
    }
    .description .answer {
        font-size: 0.9rem;
    }
</style>