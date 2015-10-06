
<script type="text/javascript">
    /**
     * Guiders are created with guiders.createGuider({settings}).
     *
     * You can show a guider with the .show() method immediately
     * after creating it, or with guiders.show(id) and the guider's id.
     *
     * guiders.next() will advance to the next guider, and
     * guiders.hideAll() will hide all guiders.
     *
     * By default, a button named "Next" will have guiders.next as
     * its onclick handler.  A button named "Close" will have
     * its onclick handler set to guiders.hideAll.  onclick handlers
     * can be customized too.
     *
     * An alternate method for creating guiders is to create them in the 
     * HTML, then call $("#guider3").guider(options);
     */

    guiders.createGuider({
        buttons: [{name: "Close" },
            {name: "Next", onclick: guiders.next}],
        description: "Helpful tips to start you off.  More detail is provided later.  Repeat these tips at any later stage by clicking 'Tips' ",
        id: "guider1",
        next: "guider2",
        overlay: true,
        title: "Tips to start you off."
    });

    guiders.createGuider({
        attachTo: "#nav-organisation", position: 6,
        buttons: [
            {name: "Close" },
            {name: "Back", onclick: guiders.prev},
            {name: "Next", onclick: guiders.next}],
        description: "Your organisation includes business address information, country of residence and billing information.",
        id: "guider2",
        next: "guider3",
        title: "Set up your organisation first."
    });

    guiders.createGuider({
        attachTo: "#nav-clinics", position: 6,
        buttons: [
            {name: "Close" },
            {name: "Back", onclick: guiders.prev},
            {name: "Next", onclick: guiders.next}],
        description: "You will always have at least one clinic.  A clinic has been created for you.  Change the name and the address details.  Also set the timezone correctly.  Add your other clinics as required.",
        id: "guider3",
        next: "guider4",
        title: "Next set up all your clinics"
    });

    guiders.createGuider({
        attachTo: "#nav-practitioners", position: 6,
        buttons: [
            {name: "Close" },
            {name: "Back", onclick: guiders.prev},
            {name: "Next", onclick: guiders.next}],
        description: "One practitioner has already been set up for you.  Fix the name and other details.  Add all other practitioners.  Assign them to their clinic. ",
        id: "guider4",
        next: "guider5",
        title: "Set up your practitioners, and assign them to a clinic."
    });

    guiders.createGuider({
        attachTo: "#nav-users", position: 6,
        buttons: [
            {name: "Close" },
            {name: "Back", onclick: guiders.prev},
            {name: "Next", onclick: guiders.next}],
        description: "One admin user has been set up already but you can add more at no extra cost. Here is also where you change the name, password or email address.",
        id: "guider5",
        next: "guider6",
        title: "Add extra users"
    });

    guiders.createGuider({
        attachTo: "#nav-agent", position: 6,
        buttons: [
            {name: "Close" },
            {name: "Back", onclick: guiders.prev},
            {name: "Next", onclick: guiders.next}],
        description: "Here is where you set up integration to the appointment book system you already have.  This permits the lateness to be updated automatically.  Here is where you enter connection details and download an agent program.",
        id: "guider6",
        next: "guider7",
        title: "Hook up your appointment book"
    });

    guiders.createGuider({
        attachTo: "#nav-main", position: 6,
        buttons: [
            {name: "Close" },
            {name: "Back", onclick: guiders.prev},
            {name: "Next", onclick: guiders.next}],
        description: "Here you can make manual adjustments to the lateness, or freeze the lateness setting for the day",
        id: "guider7",
        next: "guider8",
        title: "Main Screen"
    });

    guiders.createGuider({
        attachTo: "#nav-devices", position: 6,
        buttons: [
            {name: "Close" },
            {name: "Back", onclick: guiders.prev},
            {name: "Next", onclick: guiders.next}],
        description: "You can view all the mobile devices which have subscribed or been invited.",
        id: "guider8",
        next: "guider9",
        title: "Devices"
    });


    guiders.createGuider({
        attachTo: "#nav-activity", position: 6,
        buttons: [
            {name: "Back"},
            {name: "Close"}],
        description: "View the activity log and any text messages sent.",
        id: "guider9",
        title: "Activity Log"
    });



    //guiders.show("guider1");


</script>
