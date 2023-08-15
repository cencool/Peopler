// photoUpload.js
// chcem odstartovat cropper az ked nahram obrazok
//
const photoInputElement = document.getElementById("photoFile");
const image = document.getElementById("image");
const resetBtn = document.getElementById("resetBtn");
const cropPreview = document.getElementById("cropPreview");
const uploadBtn = document.getElementById("uploadBtn");
const formFileInput = document.getElementById("photoupload-imagefile");
const alertMessage = document.getElementById("forMessage");

let cropperStarted = false;
let cropper = {};
let newFile = "";
let myBlob = "";
let newFileName = "";
let newFileType = "";
const myForm = new FormData();

photoInputElement.addEventListener("change", handleFile, false);

resetBtn.addEventListener("click", () => {
  console.log("reset pressed");
  cropper.reset();
});

uploadBtn.addEventListener("click", uploadPhoto);

image.addEventListener("cropend", updatePreview);

function createCropper() {
  cropperStarted = true;
  return new Cropper(image, {
    crop(event) {
      console.log(event.detail.x);
      console.log(event.detail.y);
      console.log(event.detail.width);
      console.log(event.detail.height);
      console.log(event.detail.rotate);
      console.log(event.detail.scaleX);
      console.log(event.detail.scaleY);
    },
    ready() {
      updatePreview();
    },
  });
}

function handleFile() {
  console.log("from handleFile");
  const fileList = this.files;
  console.log(fileList[0].type, ":", fileList[0].name);
  const reader = new FileReader();
  reader.onload = (e) => {
    image.src = e.target.result;
    if (!cropperStarted) {
      cropper = createCropper();
    }
    cropper.replace(image.src);
    cropper.reset();
    newFileName = "cropped_" + photoInputElement.files[0].name;
    newFileType = photoInputElement.files[0].type;
    console.log("file type: ", newFile.type);
  };
  reader.readAsDataURL(fileList[0]);
}

function updatePreview() {
  URL.revokeObjectURL(cropPreview.src);
  cropper.getCroppedCanvas({ fillColor: "#FFF" }).toBlob((blob) => {
    cropPreview.src = URL.createObjectURL(blob);
    myBlob = blob;
    console.log("inside blob size: ", myBlob.size);
    assignFile();
  }, newFileType);
}

function assignFile() {
  newFile = new File([myBlob], newFileName, {
    type: newFileType,
  });

  console.log("crop blob size: ", myBlob.size);
  console.log("crop file size: ", newFile.size);
  console.log("file type: ", newFile.type);
}

function uploadPhoto() {
  let currentURL = new URL(window.location.href);
  let id = currentURL.searchParams.get("id");
  console.log("file size: ", newFile.size);
  myForm.set("_csrf", _csrf);
  myForm.set("subor", newFile);
  myForm.set("id", id);
  fetch(window.location.origin + "/photo/receive", {
    method: "POST",
    body: myForm,
    redirect: "follow",
  })
    .then((response) => {
      if (response.redirected) {
        console.log("response: " + response.url);
        window.location.href = response.url;
        //throw new Error("request redirected");
      } else {
        return response.text();
      }
    })
    .then((text) => {
      console.log(text);
    })
    .catch((e) => {
      console.log("Mame tu chybu :" + e);
    });
}
