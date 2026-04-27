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

let Utils = {
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

    removeFromStorageList: function (key, id) {
        var list = Utils.getStorageList(key);

        list.splice(list.indexOf(id), 1);

        sessionStorage.setObject('list.' + key, list);
    },

    existsInStorageList: function (key, id) {
        var list = Utils.getStorageList(key);
        return (list && list.includes(id));
    },

    clearStorageList: function (key) {
        sessionStorage.setObject('list.' + key, []);
    },
}

/** List toggle */
let ListToggle = {
    init: function () {
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

    refreshFormList: function () {
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

    refreshCounters: function () {
        document.querySelectorAll('span.list_toggle').forEach(counter => {
            counter.innerHTML = Utils.getStorageList(counter.dataset.listId).length;
        });
    }
}

/* Form Toggle */
let FormToggle = {
    init: function () {
        document.querySelectorAll('.form-dependency').forEach(toggle => {
            toggle.addEventListener('change', () => {
                this.refresh(toggle);
            });

            this.refresh(toggle);
        });
    },

    refresh: function (toggle) {
        const target = document.querySelector(toggle.dataset.target);
        console.log(target);
        if (target) {
            target.disabled = !toggle.checked;

            if (toggle.dataset.required !== undefined) {
                target.required = toggle.checked;
            }
        }
    }
};

document.addEventListener("DOMContentLoaded", function (event) {
    ListToggle.init();
    FormToggle.init();
});

function digitFilled(element) {
    if (element.value.length == 1) {
        element.nextElementSibling.focus();
    } else if (element.value.length == 4 && element.previousElementSibling == undefined) {
        var spread = new String(element.value);
        element.value = spread[0];
        element.nextElementSibling.value = spread[1];
        element.nextElementSibling.nextElementSibling.value = spread[2];
        element.nextElementSibling.nextElementSibling.nextElementSibling.value = spread[3];
    } else {
        element.value = '';
    }
}

function copyValueTo(from, to, append) {
    if (to.value == '') {
        let value = from.value;

        if (append) {
            value += append;
        }

        to.value = value;
    }
}

function setCheckboxValue(name, value) {
    let checkbox = document.getElementsByName(name)[0];

    if (checkbox) {
        checkbox.checked = value;
    }
}
