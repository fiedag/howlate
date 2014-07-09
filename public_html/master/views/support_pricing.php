<html>


    <?php $controller->get_header(); ?>

    <body>

        <div class="container primary-content">

            <h1>Pricing Summary </h1>
            <div class="clearb"></div>

            <div class="span-24 append-bottom-half">
                <div class="rounded-container">
                    <div class="content clearfix">
                            Single Practitioners in a small surgery are free and always will be.  Above that you should never expect to pay more
                            than roughly the cost of a mobile phone plan per clinic.  
                            
                            
                            Single clinics will pay between $20 and $40 per month (depending on size).
                            Up to 4 clinics will cost between $15 and $30 per clinic per month.
                            If you run more than 5 clinics this will cost between $12 and $24 per clinic per month.
                            
                            The number and size of clinics is calculated at the start of a billing period.
                      
                            <br><br>
                            <table class="pricing">
                                <thead>
                                    <tr><td>Clinics</td><td>Single Clinic</td><td>Up to 4 clinics</td><td>5 and more clinics</td></tr>
                                </thead>

                            <tbody>
                                <tr><td>Sole Practitioner</td><td>FREE</td><td></td><td></td></tr>
                                <tr><td>Small Clinic (up to 4)</td><td>20</td><td>15</td><td>12</td></tr>
                                <tr><td>Large Clinic (5 to 20)</td><td>30</td><td>23</td><td>18</td></tr>
                                <tr><td>Huge Clinic (20+)</td><td>40</td><td>30</td><td>24</td></tr>
                            </tbody>
                            </table>                            


                        <div class="span-10">

                            <table>
                                <tbody>
                                    <tr>
                                        <td><strong>You have</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Huge Clinics (over 20 practitioners)</td>
                                        <td><?php echo $num_clinics; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Large Clinics (5-20 practitioners)</td>
                                        <td><?php echo $num_clinics; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Small Clinics (2-4 practitioners)</td>
                                        <td><?php echo $num_clinics; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Unassigned Practitioners</strong></td>
                                        <td><?php echo $num_practitioners; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last 3 months bills</strong></td>
                                    <tr>
                                        <td><strong>6 July 2014</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>6 June 2014</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>6 May 2014</strong></td>
                                    </tr>
                                    <tr>
                                        <td><a href="/bills/all">earlier</a></td>
                                    </tr>

                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="prepend-2 span-12 prepend-top-1 append-bottom-1">
                <h4>Need Help? Have Questions?</h4>
                <div class="prepend-top-half fs_11">
                    <strong>Toll-free in North America</strong><br>
                    1-800-HOW-LATE
                </div>
                <div class="prepend-top-half fs_11">
                    <strong>Toll-free in Australia</strong><br>
                    1800-HOW-LATE
                </div>
                <div class="prepend-top-half fs_11">
                    <strong>Worldwide</strong><br>
                    +61-87324-0902
                </div>
                <div class="prepend-top-half fs_11">
                    Monday–Friday, 10:30pm–7:30am your local (Australia/Adelaide) time
                </div>
                <div class="clearb"></div>
            </div>

    
            
            
            
        </div>


    </body>



    <?php $controller->get_footer(); ?>    

</html>