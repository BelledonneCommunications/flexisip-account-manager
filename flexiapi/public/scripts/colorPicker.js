let colorPicker = {
    init() {
        let hue = document.getElementById("theme_hue").value;
        document.getElementById('hex-color').value = this.hslToHex(hue, 100, 50);
        document.getElementById('color-div').style.backgroundColor = this.hslToHex(hue, 100, 50);
    },

    onSliderChange(input) {
        const group = input.closest('.color-picker-group');
        const hue = input.value;

        group.querySelector('input[type=hidden]').value = hue;
        group.querySelector('input[type=text]').value = this.hslToHex(hue, 100, 50);
        group.querySelector('.color-div').style.backgroundColor = this.hslToHex(hue, 100, 50);
    },

    onInputChange(input) {
        const hex = input.value.trim();
        if (!hex.match(/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/)) {
            return;
        }

        const group = input.closest('.color-picker-group');
        const colorHsl = this.hexToHsl(hex);
        group.querySelector('input[type=hidden]').value = colorHsl.h;
        group.querySelector('input[type=range]').value = colorHsl.h;
        group.querySelector('input[type=text]').value = this.hslToHex(colorHsl.h, 100, 50);
        group.querySelector('.color-div').style.backgroundColor = this.hslToHex(colorHsl.h, 100, 50);
    },

    onReset(i, hue) {
        const color = this.hslToHex(hue, 100, 50);
        const group = i.closest('.color-picker-group');
        group.querySelector('input[type=hidden]').value = hue;
        group.querySelector('input[type=range]').value = hue;
        group.querySelector('input[type=text]').value = color;
        group.querySelector('.color-div').style.backgroundColor = color;
    },

    hslToHex(h, s, l) {
        s /= 100;
        l /= 100;

        const k = n => (n + h / 30) % 12;
        const a = s * Math.min(l, 1 - l);
        const f = n => Math.round(255 * (l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)))));

        const toHex = v => v.toString(16).padStart(2, '0');
        return `#${toHex(f(0))}${toHex(f(8))}${toHex(f(4))}`;
    },

    hexToHsl(hex) {
        hex = hex.replace(/^#/, '');
        if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');

        const r = parseInt(hex.slice(0, 2), 16) / 255;
        const g = parseInt(hex.slice(2, 4), 16) / 255;
        const b = parseInt(hex.slice(4, 6), 16) / 255;

        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        const d = max - min;
        let h, s;
        let l = (max + min) / 2;

        if (d === 0) {
            h = s = 0;
        } else {
            s = d / (l > 0.5 ? 2 - max - min : max + min);
            switch (max) {
                case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                case g: h = ((b - r) / d + 2) / 6; break;
                case b: h = ((r - g) / d + 4) / 6; break;
            }
        }

        return {
            h: Math.round(h * 360),
            s: Math.round(s * 100),
            l: Math.round(l * 100),
        };
    }
}

let imagePicker = {
    init() {
        document.querySelectorAll('.image-picker-group').forEach(group => {
            const canvas = group.querySelector('canvas');
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            const ctx = canvas.getContext("2d");
            const existingImage = canvas.dataset.existing;

            if (existingImage) {
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.onload = () => this.drawImageProp(ctx, img);
                img.src = existingImage;
            } else {
                this.setPlaceHolder(canvas);
            }
        });
    },

    onLoad(input) {
        const file = input.files[0];
        if (!file) return;
        if (!file.type.match('image.*')) {
            console.log("Error: not an image");
            imgInput.value = "";
            return;
        }

        const url = URL.createObjectURL(file);
        const img = new Image();

        const group = input.closest('.image-picker-group');
        const canvas = group.querySelector('canvas');
        const ctx = canvas.getContext("2d");

        img.onload = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            this.drawImageProp(ctx, img);
            URL.revokeObjectURL(url);

            canvas.toBlob((blob) => {
                const croppedFile = new File([blob], 'logo.png', { type: 'image/png' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                input.files = dataTransfer.files;
            }, 'image/png');
        };

        img.src = url;
        group.querySelector('input[name=logo_delete]').value = 0;
        group.querySelector('input[type=button]').style.display = 'block';
    },

    drawImageProp(ctx, img) {
        const canvas = ctx.canvas;
        const scale = Math.max(canvas.width / img.width, canvas.height / img.height);

        const nw = img.width * scale;
        const nh = img.height * scale;

        const x = (canvas.width - nw) / 2;
        const y = (canvas.height - nh) / 2;

        ctx.drawImage(img, x, y, nw, nh);
    },

    setPlaceHolder(canvas) {
        const ctx = canvas.getContext("2d");
        const style = getComputedStyle(document.querySelector('body'));
        ctx.fillStyle = style.getPropertyValue('--second-5').trim();
        ctx.font = `${canvas.width / 6}px ${style.getPropertyValue('font-family')}`;
        ctx.textBaseline = "middle";

        const text = "Image";
        const textWidth = ctx.measureText(text).width;
        ctx.fillText(text, (canvas.width - textWidth) / 2, canvas.height / 2);
    },

    onDelete(input) {
        const group = input.closest('.image-picker-group');
        const canvas = group.querySelector('canvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        this.setPlaceHolder(canvas);
        group.querySelector('input[name=logo_delete]').value = 1;
        group.querySelector('input[type=button]').style.display = 'none';
    }
}

document.addEventListener("DOMContentLoaded", () => {
    colorPicker.init();
    imagePicker.init();
});
