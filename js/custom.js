const imageInput = document.getElementById('image');
const imagePreview = document.getElementById('imagePreview');
const previewText = document.getElementById('previewText');

imageInput.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.addEventListener('load', function () {
            imagePreview.setAttribute('src', this.result);
            imagePreview.style.display = 'block';
            previewText.style.display = 'none';
        });
        reader.readAsDataURL(file);
    } else {
        imagePreview.style.display = 'none';
        previewText.style.display = 'block';
    }
});