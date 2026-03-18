export default {
    data() {
        return {
            isLoading: false, // Tracks loading state
            error: null, // Stores error messages
        };
    },
    methods: {
        async validateKey(apiKey) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'woo_mailerlite_handle_connect_account',
                        apiKey: apiKey,
                        nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
                    }),
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Invalid API Key');
                    }
                    throw new Error(`Error: ${response.status} - ${response.statusText}`);
                }

                return await response.json();
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.isLoading = false;
            }
        },
        async resetResources() {
            try {
                const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'woo_mailerlite_reset_sync_handler',
                        nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
                    }),
                });

                if (!response.ok) {
                    throw new Error(`Error: ${response.status} - ${response.statusText}`);
                }

                return await response.json();
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.isLoading = false;
            }
        },
        async syncUntrackedResources() {
            try {
                const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'handle_sync_resources',
                        nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
                    }),
                });

                if (!response.ok) {
                    throw new Error(`Error: ${response.status} - ${response.statusText}`);
                }

                return await response.json();
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.isLoading = false;
            }
        },
        async saveSettings(settings) {
            try {
                let requestData = {
                    action: 'handle_save_settings',
                    settings: JSON.stringify(settings),
                    nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
                };

                const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(requestData).toString()
                });

                if (!response.ok) {
                    throw new Error(`Error: ${response.status} - ${response.statusText}`);
                }

                return await response.json();
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.isLoading = false;
            }
        },
        async getDebugLog() {
            try {
                const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'handle_debug_log',
                        nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
                    }),
                });
                if (!response.ok) {
                    throw new Error(`Error: ${response.status} - ${response.statusText}`);
                }

                return await response.json();
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.isLoading = false;
            }
        },
        async createGroupApi(group) {
            const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'woo_mailerlite_create_group',
                    group: group,
                    nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
                }),
            });
            return await response.json()
        },
        async setupShop(group, syncFields, consumerKey = "", consumerSecret = "") {
            let body = {
                action: 'woo_mailerlite_shop_setup',
                group: group,
                syncFields: syncFields,
                nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
            };
            if (consumerKey && consumerSecret) {
                body.consumerKey = consumerKey;
                body.consumerSecret = consumerSecret;
            }
            try {
                let response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(body),
                });
                response = await response.json();
                if (!response.success) {
                    throw new Error(response.message ?? 'Error: ' + response.status);
                }
                return response;
            } catch (err) {
                // this.error = err.message;
                throw err;
            } finally {
                this.isLoading = false;
            }
        },
        async resetIntegrationSettings() {
            const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'woo_mailerlite_reset_integration_settings',
                    nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
                }),
            });
            return await response.json()
        },
        async downgrade() {
            const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'woo_mailerlite_downgrade_plugin',
                    nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
                }),
            });
            return await response.json()
        },
        async debugModeEnable() {
            const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'woo_mailerlite_enable_debug_mode',
                    nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce
                }),
            });
            return response.status === 200;
        }
    },
};