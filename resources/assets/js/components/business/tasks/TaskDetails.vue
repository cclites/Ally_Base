<template>
    <b-container fluid>
        <b-row class="with-padding-bottom">
            <b-col><h4>{{ task.name }}</h4></b-col>
        </b-row>
        <b-row class="with-padding-bottom">
            <b-col sm="12">
                <strong>Notes</strong><br />
                <p v-if="task.notes" v-html="$options.filters.nl2br(task.notes)" />
            </b-col>
        </b-row>
        <b-row class="with-padding-bottom">
            <b-col sm="6">
                <strong>Created</strong><br />
                {{ formatDateTimeFromUTC(task.created_at) }}
            </b-col>
            <b-col sm="6">
                <strong>Created By</strong><br />
                <span v-if="task.creator">{{ task.creator.name }}</span>
            </b-col>
        </b-row>
        <b-row class="with-padding-bottom">
            <b-col sm="6">
                <strong>Due Date</strong><br />
                <span v-if="task.due_date">{{ formatDateTimeFromUTC(task.due_date) }}</span>
                <span v-else class="text-muted">Not Set</span>
            </b-col>
            <b-col sm="6">
                <strong>Status</strong><br />
                <span v-if="task.completed_at">Completed on {{ formatDateTimeFromUTC(task.completed_at) }}</span>
                <span v-else>Incomplete</span>
            </b-col>
        </b-row>
        <b-row class="with-padding-bottom">
            <b-col sm="6">
                <strong>Assigned To</strong><br />
                <span v-if="task.assigned_user_id">{{ task.assigned_user.name }} ({{ task.assigned_type }})</span>
                <span v-else class="text-muted">Not Assigned</span>
            </b-col>
        </b-row>
        <b-row class="with-padding-bottom" v-if="task.last_edit">
            <b-col sm="12">
                <strong>Last Edited</strong><br />
                {{ formatDateTimeFromUTC(task.last_edit.created_at) }} by {{ task.last_edit.edited_by }}
            </b-col>
        </b-row>
    </b-container>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        mixins: [ FormatsDates ],

        props: {
            task: {
                type: Object,
                default() {
                    return {};
                }
            },
        },

        methods: {
        }
    }
</script>
