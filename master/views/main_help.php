

<div id="helpbubble_holder"></div>


<script src="/js/libs/Ractive.min.js"></script>
<script src="/js/libs/lodash.js"></script>
<script src="/js/helpbubbles.min.js"></script>
<script src="/js/helpbubbles.fix.js"></script>


<script>
    var bubblecious;
    bubblecious = new window.HelpBubbles({
        el: 'helpbubble_holder',
        data: {
            y_adjustment: 20,
            bubbles: [
                {
                    target: 'nav-main',
                    content: 'Here you can manually override practitioners lateness',
                },
                {
                    target: 'nav-devices',
                    content: 'Devices are e.g. mobile phones which have been registered',
                },
                {
                    target: 'nav-practitioners',
                    content: 'Practitioners are created by the agent process, and must be assigned to a clinic',
                },
                {
                    target: 'nav-clinics',
                    content: 'One clinic is created by default but you must fill in address details, timezone etc.',
                },
                {
                    target: 'nav-organisation',
                    content: 'Update your organisation details, upload a logo etc.',
                },
                {
                    target: 'nav-users',
                    content: 'Create users who can sign on to this site, or reset passwords.',
                },
                {
                    target: 'nav-agent',
                    content: 'Appointment types, sessions, agent connection details.  Download agent.',
                },
                {
                    target: 'nav-activity',
                    content: 'Activity Log records and SMS logs.',
                },
                {
                    target: 'nav-support',
                    content: 'Contact the HOW-LATE Team',
                    onTap: function () {
                        console.log('Alt Bubble-tapped');
                    }
                }
            ]
        }
    });



</script>
