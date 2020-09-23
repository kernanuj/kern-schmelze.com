export default {
    namespaced: true,

    state() {
        return {
            loading: false,
            config: {},
            actualConfigData: {},
            salesChannelModel: null,
            currentSalesChannelId: null
        };
    },

    getters: {
        isLoading: (state) => {
            return state.loading;
        }
    },

    mutations: {
        setLoading(state, value) {

            if (typeof value !== 'boolean') {
                return false;
            }

            state.loading = value;

            return false;
        },
        setConfig(state, config) {
            state.config = config;
        },
        setCurrentSalesChannelId(state,channelId) {
            state.currentSalesChannelId = channelId;
        }

    }
};
