/**
 * @brief Set object in localStorage
 * @param key string
 * @param value the object
 */
Storage.prototype.setObject = function (key, value) {
    this.setItem(key, JSON.stringify(value));
};

/**
 * @brief Get object in localStorage
 * @param key
 */
Storage.prototype.getObject = function (key) {
    return JSON.parse(this.getItem(key));
};

var Utils = {
    toggleAll: function (checkbox) {
        checkbox.closest('table').querySelectorAll('tbody input[type=checkbox]').forEach(element => {
            element.checked = checkbox.checked;
            element.dispatchEvent(new Event('change'));
        });
    },

    getStorageList: function (key) {
        var list = sessionStorage.getObject('list.' + key);

        if (list == null) {
            list = [];
        }

        return list;
    },

    addToStorageList: function (key, id) {
        var list = Utils.getStorageList(key);

        if (!list.includes(id)) {
            list.push(id);
        }

        sessionStorage.setObject('list.' + key, list);
    },

    removeFromStorageList: function(key, id) {
        var list = Utils.getStorageList(key);

        list.splice(list.indexOf(id), 1);

        sessionStorage.setObject('list.' + key, list);
    },

    existsInStorageList: function(key, id) {
        var list = Utils.getStorageList(key);
        return (list && list.includes(id));
    },

    clearStorageList: function (key) {
        sessionStorage.setObject('list.' + key, []);
    },

    /** List toggle */
}

var ListToggle = {
    init: function() {
        document.querySelectorAll('input[type=checkbox].list_toggle').forEach(checkbox => {
            checkbox.checked = Utils.existsInStorageList(checkbox.dataset.listId, checkbox.dataset.id);

            checkbox.addEventListener('change', e => {
                if (checkbox.checked) {
                    Utils.addToStorageList(checkbox.dataset.listId, checkbox.dataset.id);
                } else {
                    Utils.removeFromStorageList(checkbox.dataset.listId, checkbox.dataset.id);
                }

                ListToggle.refreshFormList();
                ListToggle.refreshCounters();
            })
        });

        ListToggle.refreshFormList();
        ListToggle.refreshCounters();
    },

    refreshFormList: function() {
        document.querySelectorAll('select.list_toggle').forEach(select => {
            select.innerHTML = '';
            select.multiple = true;

            Utils.getStorageList(select.dataset.listId).forEach(id => {
                const option = document.createElement("option");
                option.value = id;
                option.text = id;
                option.selected = true;
                select.add(option, null);
            });
        });
    },

    refreshCounters: function() {
        document.querySelectorAll('span.list_toggle').forEach(counter => {
            counter.innerHTML = Utils.getStorageList(counter.dataset.listId).length;
        });
    }
}

document.addEventListener("DOMContentLoaded", function(event) {
    ListToggle.init();
});

function digitFilled(element) {
    if (element.value.length == 1) {
        element.nextElementSibling.focus();
    }
}