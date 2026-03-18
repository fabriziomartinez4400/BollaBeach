const eventBus = Vue.reactive({});

eventBus.on = function (event, callback) {
    if (!this[event]) this[event] = [];
    this[event].push(callback);
};

eventBus.emit = function (event, payload) {
    if (this[event]) {
        this[event].forEach((callback) => callback(payload));
    }
};

export default eventBus;