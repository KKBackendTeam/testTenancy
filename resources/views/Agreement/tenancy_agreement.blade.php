<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
        body {
            margin: 0;
            font-family: 'Times New Roman', Times, serif;
        }

        li {
            list-style: none;
        }

        a {
            text-decoration: none;
        }

        .Agreement_main {
            margin: 0 auto;
            width: 712px;
        }

        .main_heading {
            text-align: center;
        }

        .main_heading h3 {
            text-decoration: underline;
            font-size: 20px;
        }

        .main_heading p {
            font-size: 18px;
            font-weight: 700;
        }

        .between {
            text-align: center;
        }

        .between h3 {
            text-align: left;
        }

        .between li {
            font-size: 18px;
            line-height: 20px;
        }

        .between li span {
            font-size: 20px;
            font-weight: 700;
        }

        .background p {
            font-size: 18px;
        }

        .background h3.title {
            font-size: 18px;
        }

        .background ul li {
            list-style: decimal;
            font-size: 18px;
            line-height: 20px;
        }

        .witness_address {
            width: 50%;
            float: left;
        }

        .witness_name {
            width: 50%;
            float: left;

        }

        .signature .name1 {
            width: 50%;
            float: left;
        }

        .signature .name2 {
            width: 50%;
            float: left;
        }

        .let_property ul li {
            list-style: decimal;
            font-size: 18px;
        }

        .let_property ul li ul li {
            list-style: decimal;
            font-size: 18px;
        }

        .let_property ul span {
            font-weight: 700;
        }

        .clearfix {
            clear: both;
        }
    </style>
</head>

<body>

<div class="Agreement_main">
    <div class="main_heading">
        <h3>Assured Shorthold Tenancy Agreement</h3>
        <p>THIS TENANCY AGREEMENT (the "Agreement") dated this 23rd day of September, 2018</p>

    </div>

    <div class="between">
        <h3 class="title">BETWEEN:</h3>
        <ul>
            <li> {{ $tenancy['landlords']['f_name'] }} {{ $tenancy['landlords']['l_name'] }} </li>
            <li>Address: {{ $tenancy['landlords']['street'] }},
                {{ $tenancy['landlords']['town'] }}  {{ $tenancy['landlords']['country'] }}</li>
            <li>Telephone: {{ $tenancy['landlords']['mobile'] }}</li>
        </ul>

        <p>(the "Landlord")</p>

        <b>­- AND ­-</b>

        <ul>
            @foreach( $tenancy['applicants'] as $applicant)

                <li>{{ $applicant['app_name'] }} {{ $applicant['m_name'] }} {{ $applicant['l_name'] }} {{--</span> and <span>Applicant 2 Full Name</span></li>
                <li>Address: <span> Applicant 1 Current Address </span> and <span>Applicant 2 Current Address</span>
                </li>
                <li>Telephone: <span> Applicant 1 Mobile Number </span> and <span>Applicant 2 Mobile Number</span></li>--}}

            @endforeach
        </ul>

        <p>(collectively and individually the "Tenant") </p>
        <p>(individually the “Party” and collectively the “Parties”) </p>
    </div>

    <div class="background">
        <p><strong>IN CONSIDERATION OF</strong> the Landlord leasing certain premises to the Tenant and other
            valuable
            consideration, the receipt and sufficiency of which consideration is hereby acknowledged, the Parties
            agree as follows: </p>

        <h3 class="title">BACKGROUND:</h3>

        <ul>
            <li>This is an agreement to create an Assured Shorthold Tenancy as defined in Section 19A of the
                Housing Act 1988 or any successolet_propertyr legislation as supplemented or amended from time to time and
                any other applicable and relevant laws and regulations
            </li>
            <li>The Landlord is the owner of residential property available for rent and is legally entitled to
                grant
                this tenancy.
            </li>
        </ul>
    </div>

    <div class="let_property">

        <ul>
            <h3 class="title">Let Property</h3>
            <li>The Landlord agrees to let to the Tenant, and the Tenant agrees to take a tenancy of the flat,
                known
                as and forming {{ $tenancy['pro_address'] }} (the "Property"), for use as residential premises
                only.
            </li>

            @if($tenancy['restriction'] != null)
                <li>Restrictions : <p> {{ $tenancy['restriction'] }}</p></li>

            @endif

            @if($tenancy['parking'] == 1)
                <li>Parking included : <p> Yes</p></li>

            @endif

            <li>The Tenant and members of Tenant's household will not smoke anywhere on the Property nor
                permit any guests or visitors to smoke on the Property.

                <h3 class="title">Term</h3>

            <li>The term of the tenancy commences on {{ $tenancy['t_start_date'] }} and ends
                on {{ $tenancy['t_end_date'] }}
                (the "Term").
            </li>

            <li>Should neither party have brought the Tenancy to an end at or before the expiry of the Term, then a
                new tenancy from month to month will be created between the Landlord and the Tenant which will
                be subject to all the terms and conditions of this Agreement but will be terminable upon the
                Landlord giving the Tenant the notice required under the under the applicable legislation of
                England (the "Act").
            </li>

            <h3 class="title">Rent</h3>

            <li>Subject to the provisions of this Agreement, the rent for the Property is
                £{{ $tenancy['monthly_amount'] }}(the "Rent").
            </li>
            <li>The Tenant will pay the Rent in advance, on or before the <span>Day of rent payment</span> and
                <span>Term of Rent
                        Payment Schedule</span>
                of the Term to the Landlord by standing order.
            </li>
            <li>The Tenant will be charged an additional amount of £25.00 per infraction, for any late Rent.</li>
            <li>The Landlord may increase the Rent for the Property upon providing to the Tenant such notice as
                required by the Act.
            </li>

            <h3 class="title">Security Deposit</h3>
            <li>On execution of this Agreement, the Tenant will pay the Landlord a security deposit
                of {{ $tenancy['deposite_amount'] }}(the "Security Deposit").
            </li>
            <li>No interest will be received on the Security Deposit.</li>
            <li>The Landlord will return the Security Deposit at the end of this tenancy, less such deductions as
                provided in this Agreement but no deduction will be made for damage due to fair wear and tear nor
                for any deduction prohibited by the Act.
            </li>
            <li>During the Term or after its termination, the Landlord may charge the Tenant or make deductions
                from the Security Deposit for any or all of the following:
                <ul>
                    <li>repair of walls due to plugs, large nails or any unreasonable number of holes in the walls
                        including the repainting of such damaged walls;
                    </li>
                    <li>repainting required to repair the results of any other improper use or excessive damage by
                        the Tenant;
                    </li>
                    <li>unplugging toilets, sinks and drains;
                    </li>
                    <li>replacing damaged or missing doors, windows, screens, mirrors or light fixtures;</li>
                    <li>repairing cuts, burns, or water damage to linoleum, rugs, and other areas;</li>
                    <li>any other repairs or cleaning due to any damage beyond fair wear and tear caused or
                        permitted by the Tenant or by any person whom the Tenant is responsible for;
                    </li>
                    <li>the cost of extermination where the Tenant or the Tenant's guests have brought or allowed
                        insects into the Property or building;
                    </li>
                    <li>repairs and replacement required where windows are left open which have caused plumbing
                        to freeze, or rain or water damage to floors or walls; and
                    </li>
                    <li> replacement of locks and/or lost keys to the Property and any administrative fees
                        associated
                        with the replacement as a result of the Tenant's misplacement of the keys; and
                    </li>
                    <li>any other purpose allowed under this Agreement or the tenancy deposit scheme in the
                        Housing Act 2004 as supplemented or amended from time to time.
                    </li>
                </ul>
                <p>For the purpose of this clause, the Landlord may charge the Tenant for professional cleaning and
                    repairs if the Tenant has not made alternate arrangements with the Landlord. </p>
            </li>

            <li>The Tenant may not use the Security Deposit as payment for the Rent without prejudice to the right
                of the Landlord to retain the Security Deposit, or any part of it, at the end of the Term in
                respect of
                any sum of rent which is in arrears at the end of the Term.
            </li>
            <li>Within the time period required by the Act after the termination of this tenancy, the Landlord will
                deliver or post the Security Deposit less any proper deductions or with further demand for payment
                to: <span>________________________________________________________,</span> or at such other place
                as
                the Tenant may advise. Any refund may be paid to any of the Tenants.
            </li>

            <h3 class="title">Access</h3>
            <li>The Landlord and the Tenant will complete, sign and date an inspection report at the beginning and
                at the end of this tenancy.
            </li>
            <li>At all reasonable times during the Term and any renewal of this Agreement, the Landlord and its
                agents may enter the Property to make inspections or repairs, or to show the Property to
                prospective tenants or purchasers in compliance with the Act.
            </li>

            <h3 class="title">Renewal of Agreement</h3>
            <li>Upon giving written notice no later than 60 days before the expiration of the Term, the Tenant may
                renew this Agreement for an additional term. All terms of the renewed agreement will be the same
                except for this renewal clause and the amount of the rent. The Landlord may increase the rent from
                the initial term. If the Parties cannot agree as to the amount of the rent, this Agreement will not
                be
                renewed and the Tenant must vacate the Property at the end of the initial Term.
            </li>

            <h3 class="title">Landlord Improvements</h3>
            <li>. The Landlord will make the following improvements to the Property: Change Carpets in all rooms
                Paint entire flat.
            </li>

            <h3 class="title">Tenant Improvements</h3>
            <li>The Tenant will obtain written permission from the Landlord before doing any of the following: '
                <ul>
                    <li>applying adhesive materials, or inserting nails or hooks in walls or ceilings other than
                        two
                        small picture hooks per wall;
                    </li>
                    <li>painting, wallpapering, redecorating or in any way significantly altering the appearance of
                        the Property;
                    </li>
                    <li>removing or adding walls, or performing any structural alterations;</li>
                    <li>installing a waterbed(s);</li>
                    <li>changing the amount of heat or power normally used on the Property as well as installing
                        additional electrical wiring or heating units;
                    </li>
                    <li>placing or exposing or allowing to be placed or exposed anywhere inside or outside the
                        Property any placard, notice or sign for advertising or any other purpose; or
                    </li>
                    <li>affixing to or erecting upon or near the Property any radio or TV antenna or tower.</li>
                </ul>
            </li>

            <h3 class="title">Utilities and Other Charges</h3>
            <li>The Tenant is responsible for the payment of all utilities in relation to the Property.
            </li>

            <h3 class="title">Insurance</h3>
            <li>The Tenant is hereby advised and understands that the personal property of the Tenant is not
                insured by the Landlord for either damage or loss, and the Landlord assumes no liability for any
                such loss.
            </li>
            <li>The Tenant is not responsible for insuring the Landlord's contents and furnishings in or about the
                Property for either damage or loss, and the Tenant assumes no liability for any such loss.
            </li>
            <li>The Tenant is not responsible for insuring the Property for either damage or loss to the structure,
                mechanical or improvements to the building of the Property, and the Tenant assumes no liability
                for any such loss.
            </li>

            <h3 class="title">Absences</h3>
            <li>The Tenant will inform the Landlord if the Tenant is to be absent from the Property for any reason
                for a period of more than 14 days. The Tenant agrees to take such measures to secure the Property
                prior to such absence as the Landlord may reasonably require and take appropriate measures to
                prevent frost or flood damage.
            </li>
            <li>If the Tenant no longer occupies the Property as its only principal home (whether or not the Tenant
                intends to return) the Landlord may, at its option, end the tenancy by serving a Notice to Quit
                that
                complies with the Act.
            </li>
            <li>If the Tenant has abandoned the Property and the Landlord is unsure whether the Tenant intends to
                return, the Landlord is entitled to apply for a court order for possession.
            </li>
            <li>If the Tenant has abandoned or surrendered the Property and the Landlord feels that the Property is
                in an insecure or urgent condition, or that electrical or gas appliances could cause damage or
                danger to the Property then the Landlord may enter the Property to carry out urgent repairs. If the
                locks have been changed for such urgent security reasons, the Landlord must attempt to provide
                notice to the Tenant of the change in locks and how they can get a new key.
            </li>
            <li>If there is implied or actual surrender of the Property by the Tenant, the Landlord may, at its
                option, enter the Property by any means without being liable for any prosecution for such entering,
                and without becoming liable to the Tenant for damages or for any payment of any kind whatever,
                and may, at the Landlord's discretion, as agent for the Tenant, let the Property, or any part of
                the
                Property, for the whole or any part of the then unexpired term, and may receive and collect all
                rent
                payable by virtue of such letting, and, at the Landlord's option, hold the Tenant liable for any
                difference between the Rent that would have been payable under this Agreement during the
                balance of the unexpired term, if this Agreement had continued in force, and the net rent for such
                period realised by the Landlord by means of the letting. Implied surrender will be deemed if the
                Tenant has left the keys behind or where the Tenant has ceased to occupy the Property and clearly
                does not intend to return.
            </li>
            <li>If the Tenant has abandoned or surrendered the Property and the Tenant has left some belongings
                on the Property, the Landlord will store the Tenant's possessions with reasonable care for a
                reasonable period of time taking into consideration the value of the items and cost to store them.
                Once the cost of storage is greater than the value of the items, such items may be disposed of by
                the Landlord.
            </li>

            <h3 class="title">Governing Law</h3>
            <li>This Agreement will be construed in accordance with and governed by the laws of England and the
                Parties submit to the exclusive jurisdiction of the English Courts.
            </li>

            <h3 class="title">Severability</h3>
            <li>If there is a conflict between any provision of this Agreement and the Act, the Act will prevail
                and
                such provisions of the Agreement will be amended or deleted as necessary in order to comply with
                the Act. Further, any provisions that are required by the Act are incorporated into this Agreement.
            </li>
            <li>. The invalidity or unenforceability of any provisions of this Agreement will not affect the
                validity
                or enforceability of any other provision of this Agreement. Such other provisions remain in full
                force and effect.
            </li>

            <h3 class="title">Amendment of Agreement</h3>
            <li>This Agreement may only be amended or modified by a written document executed by the Parties.</li>


            <h3 class="title">Assignment and Subletting</h3>
            <li>The Tenant will not assign this Agreement, or sublet or grant any concession or licence to use the
                Property or any part of the Property. Any assignment, subletting, concession, or licence, whether
                by operation of law or otherwise, will be void and will, at Landlord's option, terminate this
                Agreement.
            </li>

            <h3 class="title">Additional Provisions</h3>
            <li>. Bikes must be stored outside the building.</li>

            <h3 class="title">Damage to Property</h3>
            <li>If the Property should be damaged other than by the Tenant's negligence or wilful act or that of
                the
                Tenant's employee, family, agent, or visitor and the Landlord decides not to rebuild or repair the
                Property, the Landlord may end this Agreement by giving appropriate notice.
            </li>

            <h3 class="title">Care and Use of Property</h3>
            <li>The Tenant will promptly notify the Landlord of any damage, or of any situation that may
                significantly interfere with the normal use of the Property.
            </li>
            <li>The Tenant will keep the Property in good repair and condition and in good decorative order.</li>
            <li>The Tenant or anyone living with the Tenant will not engage in any illegal trade or activity on or
                about the Property including, but not limited to, using the Property for drug storage, drug
                dealing,
                prostitution, illegal gambling or illegal drinking.
            </li>
            <li>The Parties will comply with standards of health, sanitation, fire, housing and safety as required
                by
                law.
            </li>
            <li>If the Tenant is absent from the Property and the Property is unoccupied for a period of 14
                consecutive days or longer, the Tenant will arrange for regular inspection by a competent person.
                The Landlord will be notified in advance as to the name, address and phone number of this said
                person.
            </li>
            <li>At the expiration of the Term, the Tenant will quit and surrender the Property in as good a state
                and
                condition as they were at the commencement of this Agreement, reasonable use and wear and
                damages by the elements excepted.
            </li>

            <h3 class="title">Rules and Regulations</h3>
            <li>The Tenant agrees to obey all reasonable rules and regulations implemented by the Landlord from
                time to time regarding the use and care of the Property and of the building, which will include any
                car park and common parts or facilities provided for the use of the Tenant and other neighbouring
                proprietors.
            </li>

            <h3 class="title">Termination of Tenancy</h3>
            <li>The Landlord may terminate the tenancy by service on the Tenant of a notice pursuant to any
                ground provided under the Act. The Landlord may serve such notice either:
                <ul>
                    <li>to terminate the tenancy at its end date (e.g. a Section 21 notice to quit),</li>
                    <li>to terminate the tenancy where the Tenant has broken or not performed any of his
                        obligations under this Agreement (e.g. a Section 8 notice of seeking possession), or
                    </li>
                    <li>to terminate the tenancy for any other ground provided in the Act (e.g. landlord is seeking
                        to live on the property again).
                    </li>
                </ul>
            </li>

            <h3 class="title">Address for Notice</h3>

            @foreach($tenancy['applicants'] as $applicant)
                <li>For any matter relating to this tenancy, the Tenant may be contacted at the Property or through the
                    phone number below:
                    <ul>
                        <li>Name:
                            {{ $applicant['app_name'] }} {{ $applicant['m_name'] }} {{ $applicant['l_name'] }}
                        </li>
                        <li>Phone: {{ $applicant['app_mobile'] }}</li>
                        <li>Email: {{ $applicant['email'] }}</li>
                    </ul>
                </li>

            @endforeach

            <li>For any matter relating to this tenancy, whether during or after this tenancy has been terminated,
                the Landlord's address for notice is:
                <ul>
                    <li>Name: {{ $tenancy['landlords']['f_name'] }} {{ $tenancy['landlords']['l_name'] }}
                    </li>
                    <li>Address: {{ $tenancy['landlords']['street'] }},
                        {{ $tenancy['landlords']['town'] }}  {{ $tenancy['landlords']['country'] }}</li>
                    <p>The contact information for the Landlord is:</p>
                    <li>Phone: {{ $tenancy['landlords']['mobile'] }}</li>
                    <li>Email: {{ $tenancy['landlords']['email'] }}</li>
                </ul>

            </li>


            <li>The Landlord or the Tenant may, on written notice to each other, change their respective addresses
                for notice under this Agreement.
            </li>

            <h3 class="title">General Provisions</h3>
            <li>Any waiver by the Landlord of any failure by the Tenant to perform or observe the provisions of
                this Agreement will not operate as a waiver of the Landlord's rights under this Agreement in
                respect of any subsequent defaults, breaches or non­performance by the Tenant of its obligations in
                this Agreement and will not defeat or affect in any way the Landlord's rights in respect of any
                subsequent default or breach.
            </li>
            <li>This Agreement will extend to and be binding upon and inure to the benefit of the respective heirs,
                executors, administrators, successors and assignees, as the case may be, of each Party to this
                Agreement. All covenants are to be construed as conditions of this Agreement.
            </li>
            <li>All sums payable by the Tenant to the Landlord pursuant to any provision of this Agreement will
                be deemed to be additional rent and will be recovered by the Landlord as rental arrears.
            </li>
            <li>Where there is more than one Tenant executing this Agreement, all Tenants are jointly and
                severally liable for each other's acts, omissions and liabilities pursuant to this Agreement.
            </li>
            <li>Locks may not be added or changed without the prior written agreement of both Parties, or unless
                the changes are made in compliance with the Act.
            </li>
            <li>If the Tenant moves out prior to the natural expiration of this Agreement, a relet levy of £250.00
                will be charged to the Tenant.
            </li>
            <li>Headings are inserted for the convenience of the Parties only and are not to be considered when
                interpreting this Agreement. Words in the singular mean and include the plural and vice versa.
                Words in the masculine mean and include the feminine and vice versa.
            </li>
            <li> This Agreement may be executed in counterparts. Facsimile signatures are binding and are
                considered to be original signatures.
            </li>
            <li>Time is of the essence in this Agreement.</li>
            <li>This Agreement will constitute the entire agreement between the Parties.</li>
            <li>During the last 30 days of this Agreement, the Landlord or the Landlord's agents will have the
                privilege of displaying the usual 'For Sale' or 'To Let' or 'Vacancy' signs on the Property and the
                Tenant agrees to allow the Landlord or its agents reasonable access to the Property at reasonable
                times for the purpose of displaying such signs upon the Property.
            </li>
            <p><strong>IN WITNESS WHEREOF</strong> Charles James, Frank Smith and Joe Bloggs have duly affixed
                their
                signatures on this 23rd day of September, 2018. </p>


        </ul>
        <div class="witness_address">
            <p>Witness: {{ $tenancy['landlords']['f_name'] }} {{ $tenancy['landlords']['l_name'] }}</p>

            <p>Address: {{ $tenancy['landlords']['street'] }},
                {{ $tenancy['landlords']['town'] }}  {{ $tenancy['landlords']['country'] }}</p>
        </div>

        <div class="witness_name">
            @if(!is_null($tenancy['signature']))
                <img src="http://127.0.0.1:8000/storage/applicant/signatures/{{ $tenancy['signature'] }}"
                     style="width:100px">
            @endif
            <p> {{ $tenancy['landlords']['f_name'] }} {{ $tenancy['landlords']['l_name'] }}</p>
        </div>
        <div class="clearfix"></div>

        @foreach($tenancy['applicants'] as $applicant)
            <div class="witness_address">
                <p>Witness: {{ $applicant->applicantReference['name'] }}</p>

                <p>Address: {{ $applicant->applicantReference['address'] }}</p>
            </div>

            <div class="witness_name">
                @if(!is_null($applicant->applicantReference['signature']))
                    <img src="{{ config('global.backSiteUrl')}}/storage/applicant/signatures/{{ $applicant->applicantReference['signature'] }}"
                         style="width:100px">
                @endif
                <p>{{ $applicant['app_name'] }} {{ $applicant['m_name'] }} {{ $applicant['l_name'] }}</p>
            </div>
            <div class="clearfix"></div>
        @endforeach

        <p>The Tenants acknowledge receiving a duplicate copy of this Agreement signed by the Tenants and the
            Landlord on the {{ $signing_date }}</p>


        @foreach($tenancy['applicants'] as $applicant)
            <div class="signature">
                <div class="name1">
                    <span></span>
                    <p>{{ $applicant['app_name'] }} {{ $applicant['m_name'] }} {{ $applicant['l_name'] }}</p>
                </div>
            </div>
            <div class="witness_name">
                @if(!is_null($applicant['agreement_signature']))
                    <img src="{{ config('global.backSiteUrl')}}/storage/applicant/agreement_signature/{{ $applicant['agreement_signature'] }}"
                         style="width:100px">
                @endif
            </div>
            <div class="clearfix"></div>
        @endforeach

    </div>

</div>
</body>

</html>
