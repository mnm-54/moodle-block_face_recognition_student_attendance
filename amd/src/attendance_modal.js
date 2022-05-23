import $ from "jquery";
import ModalFactory from "core/modal_factory";
import Webcam from "./webcam";

export const init = () => {
  window.console.log("we have been started");
  $(".action-modal").on("click", function () {
    window.console.log(`working`);

    ModalFactory.create({
      type: ModalFactory.types.SAVE_CANCEL,
      title: "Turn on webcam",
      body: `
      <p>webcam will be turned on to take video and image input for your attendance</p>
      <button id='start-webcam' class="btn btn-primary">Start Webcam</button>
      <button id='stop-webcam' class="btn btn-secondary">Cancel</button>
      <video id="webcam" autoplay playsinline width="640" height="480"></video>
      <canvas id="canvas" class="d-none"></canvas>
      <audio id="snapSound" src="audio/snap.wav" preload = "auto"></audio>`,
    }).then(function (modal) {
      modal.setSaveButtonText("Start Webcam");

      modal.show();
      $(".modal-footer").hide();

      const webcamElement = document.getElementById("webcam");
      const canvasElement = document.getElementById("canvas");
      const snapSoundElement = document.getElementById("snapSound");
      let webcam = new Webcam(
        webcamElement,
        "user",
        canvasElement,
        snapSoundElement
      );

      $("#start-webcam").on("click", function () {
        webcam
          .start()
          .then((result) => {
            window.console.log("webcam started", result);
          })
          .catch((err) => {
            window.console.log(err);
          });
      });
      $("#stop-webcam").on("click", function () {
        webcam.stop();
        window.location.href = $(location).attr("href");
      });
    });
  });
};
