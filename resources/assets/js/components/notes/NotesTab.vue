<template>
    <div>
        <b-card v-for="note in notes"
                class="mb-3"
                header-tag="header">
                <div slot="header">
                    <b-row align-h="between">
                        <b-col>
                            Created By: {{ note.creator.name }}
                        </b-col>
                        <b-col>
                            <div class="pull-right">{{ formatDate(note.created_at) + ' ' + formatTime(note.created_at) }}</div>
                        </b-col>
                    </b-row>
                </div>
            {{ note.body }}
        </b-card>

        <b-card v-if="!notes.length">
            No notes.
        </b-card>
    </div>
</template>

<style lang="scss">
</style>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    export default {
        props: ['notes'],

        mixins: [FormatsDates],

        data() {
            return{

            }
        }
    }
</script>