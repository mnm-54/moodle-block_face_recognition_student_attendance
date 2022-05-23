import $ from "jquery";
import ModalFactory from "core/modal_factory";
import ModalEvents from "core/modal_events";
import Webcam from "./webcam";

export const init = () => {
  window.console.log("we have been started");
  $(".action-modal").on("click", function () {
    window.console.log(`working`);

    ModalFactory.create({
      type: ModalFactory.types.SAVE_CANCEL,
      title: "Turn on webcam",
      body: `<video id="webcam" autoplay playsinline width="640" height="480"></video>
            <canvas id="canvas" class="d-none"></canvas>
            <audio id="snapSound" src="audio/snap.wav" preload = "auto"></audio>`,
    }).then(function (modal) {
      modal.setSaveButtonText("Start Webcam");

      modal.show();

      const webcamElement = document.getElementById("webcam");
      const canvasElement = document.getElementById("canvas");
      const snapSoundElement = document.getElementById("snapSound");
      let webcam = new Webcam(
        webcamElement,
        "user",
        canvasElement,
        snapSoundElement
      );
    });
  });
};
