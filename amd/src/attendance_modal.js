import $ from "jquery";
import ModalFactory from "core/modal_factory";
import ModalEvents from "core/modal_events";

export const init = () => {
  window.console.log("we have been started");
  $(".action-modal").on("click", function () {
    window.console.log(`working`);

    ModalFactory.create({
      type: ModalFactory.types.SAVE_CANCEL,
      title: "Turn on webcam",
      body: " webcam will be turned on to take video and image input for your attendance",
    }).then(function (modal) {
      modal.setSaveButtonText("Start Webcam");

      modal.show();
    });
  });
};
