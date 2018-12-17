export default {
    data() {
        return {
            metersToMilesRatio: 1609.344,
        };
    },

    methods: {
        convertToMiles(distance, shouldFormat = true) {
            const result = distance / this.metersToMilesRatio;
            return shouldFormat ? Number(result).toFixed(2) : result;
        },

        convertToMeters(distance, shouldFormat = true) {
            const result = distance * this.metersToMilesRatio;
            return shouldFormat ? Number(result).toFixed(2) : result;
        }
    },
}