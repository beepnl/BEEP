app.directive('countrySelect', ['$rootScope', function($rootScope) {
    return {
      restrict: 'EA',
      template:
        '<select ng-model="model" name="country_code" class="form-control">'+
                    '<option value="" label="Select a country..." selected="selected">Select a country...</option>'+
                    '<optgroup id="country-optgroup-Europe" label="Europe">'+
                      '<option value="al" label="Albania">Albania</option>'+
                      '<option value="ad" label="Andorra">Andorra</option>'+
                      '<option value="at" label="Austria">Austria</option>'+
                      '<option value="by" label="Belarus">Belarus</option>'+
                      '<option value="be" label="België">Belgium</option>'+
                      '<option value="ba" label="Bosnia and Herzegovina">Bosnia and Herzegovina</option>'+
                      '<option value="bg" label="Bulgaria">Bulgaria</option>'+
                      '<option value="hr" label="Croatia">Croatia</option>'+
                      '<option value="cy" label="Cyprus">Cyprus</option>'+
                      '<option value="cz" label="Czech Republic">Czech Republic</option>'+
                      '<option value="dk" label="Denmark">Denmark</option>'+
                      '<option value="dd" label="East Germany">East Germany</option>'+
                      '<option value="ee" label="Estonia">Estonia</option>'+
                      '<option value="fo" label="Faroe Islands">Faroe Islands</option>'+
                      '<option value="fi" label="Finland">Finland</option>'+
                      '<option value="fr" label="France">France</option>'+
                      '<option value="de" label="Germany">Germany</option>'+
                      '<option value="gi" label="Gibraltar">Gibraltar</option>'+
                      '<option value="gr" label="Greece">Greece</option>'+
                      '<option value="gg" label="Guernsey">Guernsey</option>'+
                      '<option value="hu" label="Hungary">Hungary</option>'+
                      '<option value="is" label="Iceland">Iceland</option>'+
                      '<option value="ie" label="Ireland">Ireland</option>'+
                      '<option value="im" label="Isle of Man">Isle of Man</option>'+
                      '<option value="it" label="Italy">Italy</option>'+
                      '<option value="je" label="Jersey">Jersey</option>'+
                      '<option value="lv" label="Latvia">Latvia</option>'+
                      '<option value="li" label="Liechtenstein">Liechtenstein</option>'+
                      '<option value="lt" label="Lithuania">Lithuania</option>'+
                      '<option value="lu" label="Luxembourg">Luxembourg</option>'+
                      '<option value="mk" label="Macedonia">Macedonia</option>'+
                      '<option value="mt" label="Malta">Malta</option>'+
                      '<option value="fx" label="Metropolitan France">Metropolitan France</option>'+
                      '<option value="md" label="Moldova">Moldova</option>'+
                      '<option value="mc" label="Monaco">Monaco</option>'+
                      '<option value="me" label="Montenegro">Montenegro</option>'+
                      '<option value="nl" label="Nederland">Netherlands</option>'+
                      '<option value="no" label="Norway">Norway</option>'+
                      '<option value="pl" label="Poland">Poland</option>'+
                      '<option value="pt" label="Portugal">Portugal</option>'+
                      '<option value="ro" label="Romania">Romania</option>'+
                      '<option value="ru" label="Russia">Russia</option>'+
                      '<option value="sm" label="San Marino">San Marino</option>'+
                      '<option value="rs" label="Serbia">Serbia</option>'+
                      '<option value="cs" label="Serbia and Montenegro">Serbia and Montenegro</option>'+
                      '<option value="sk" label="Slovakia">Slovakia</option>'+
                      '<option value="si" label="Slovenia">Slovenia</option>'+
                      '<option value="es" label="Spain">Spain</option>'+
                      '<option value="sj" label="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>'+
                      '<option value="se" label="Sweden">Sweden</option>'+
                      '<option value="ch" label="Switzerland">Switzerland</option>'+
                      '<option value="ua" label="Ukraine">Ukraine</option>'+
                      '<option value="su" label="Union of Soviet Socialist Republics">Union of Soviet Socialist Republics</option>'+
                      '<option value="gb" label="United Kingdom">United Kingdom</option>'+
                      '<option value="va" label="Vatican City">Vatican City</option>'+
                      '<option value="ax" label="Åland Islands">Åland Islands</option>'+
                    '</optgroup>'+
                    '<optgroup id="country-optgroup-Africa" label="Africa">'+
                      '<option value="dz" label="Algeria">Algeria</option>'+
                      '<option value="ao" label="Angola">Angola</option>'+
                      '<option value="bj" label="Benin">Benin</option>'+
                      '<option value="bw" label="Botswana">Botswana</option>'+
                      '<option value="bf" label="Burkina Faso">Burkina Faso</option>'+
                      '<option value="bi" label="Burundi">Burundi</option>'+
                      '<option value="cm" label="Cameroon">Cameroon</option>'+
                      '<option value="cv" label="Cape Verde">Cape Verde</option>'+
                      '<option value="cf" label="Central African Republic">Central African Republic</option>'+
                      '<option value="td" label="Chad">Chad</option>'+
                      '<option value="km" label="Comoros">Comoros</option>'+
                      '<option value="cg" label="Congo - Brazzaville">Congo - Brazzaville</option>'+
                      '<option value="cd" label="Congo - Kinshasa">Congo - Kinshasa</option>'+
                      '<option value="ci" label="Côte d’Ivoire">Côte d’Ivoire</option>'+
                      '<option value="dj" label="Djibouti">Djibouti</option>'+
                      '<option value="eg" label="Egypt">Egypt</option>'+
                      '<option value="gq" label="Equatorial Guinea">Equatorial Guinea</option>'+
                      '<option value="er" label="Eritrea">Eritrea</option>'+
                      '<option value="et" label="Ethiopia">Ethiopia</option>'+
                      '<option value="ga" label="Gabon">Gabon</option>'+
                      '<option value="gm" label="Gambia">Gambia</option>'+
                      '<option value="gh" label="Ghana">Ghana</option>'+
                      '<option value="gn" label="Guinea">Guinea</option>'+
                      '<option value="gw" label="Guinea-Bissau">Guinea-Bissau</option>'+
                      '<option value="ke" label="Kenya">Kenya</option>'+
                      '<option value="ls" label="Lesotho">Lesotho</option>'+
                      '<option value="lr" label="Liberia">Liberia</option>'+
                      '<option value="ly" label="Libya">Libya</option>'+
                      '<option value="mg" label="Madagascar">Madagascar</option>'+
                      '<option value="mw" label="Malawi">Malawi</option>'+
                      '<option value="ml" label="Mali">Mali</option>'+
                      '<option value="mr" label="Mauritania">Mauritania</option>'+
                      '<option value="mu" label="Mauritius">Mauritius</option>'+
                      '<option value="yt" label="Mayotte">Mayotte</option>'+
                      '<option value="ma" label="Morocco">Morocco</option>'+
                      '<option value="mz" label="Mozambique">Mozambique</option>'+
                      '<option value="na" label="Namibia">Namibia</option>'+
                      '<option value="ne" label="Niger">Niger</option>'+
                      '<option value="ng" label="Nigeria">Nigeria</option>'+
                      '<option value="rw" label="Rwanda">Rwanda</option>'+
                      '<option value="re" label="Réunion">Réunion</option>'+
                      '<option value="sh" label="Saint Helena">Saint Helena</option>'+
                      '<option value="sn" label="Senegal">Senegal</option>'+
                      '<option value="sc" label="Seychelles">Seychelles</option>'+
                      '<option value="sl" label="Sierra Leone">Sierra Leone</option>'+
                      '<option value="so" label="Somalia">Somalia</option>'+
                      '<option value="za" label="South Africa">South Africa</option>'+
                      '<option value="sd" label="Sudan">Sudan</option>'+
                      '<option value="sz" label="Swaziland">Swaziland</option>'+
                      '<option value="st" label="São Tomé and Príncipe">São Tomé and Príncipe</option>'+
                      '<option value="tz" label="Tanzania">Tanzania</option>'+
                      '<option value="tg" label="Togo">Togo</option>'+
                      '<option value="tn" label="Tunisia">Tunisia</option>'+
                      '<option value="ug" label="Uganda">Uganda</option>'+
                      '<option value="eh" label="Western Sahara">Western Sahara</option>'+
                      '<option value="zm" label="Zambia">Zambia</option>'+
                      '<option value="zw" label="Zimbabwe">Zimbabwe</option>'+
                    '</optgroup>'+
                    '<optgroup id="country-optgroup-Americas" label="Americas">'+
                      '<option value="ai" label="Anguilla">Anguilla</option>'+
                      '<option value="ag" label="Antigua and Barbuda">Antigua and Barbuda</option>'+
                      '<option value="ar" label="Argentina">Argentina</option>'+
                      '<option value="aw" label="Aruba">Aruba</option>'+
                      '<option value="bs" label="Bahamas">Bahamas</option>'+
                      '<option value="bb" label="Barbados">Barbados</option>'+
                      '<option value="bz" label="Belize">Belize</option>'+
                      '<option value="bm" label="Bermuda">Bermuda</option>'+
                      '<option value="bo" label="Bolivia">Bolivia</option>'+
                      '<option value="br" label="Brazil">Brazil</option>'+
                      '<option value="vg" label="British Virgin Islands">British Virgin Islands</option>'+
                      '<option value="ca" label="Canada">Canada</option>'+
                      '<option value="ky" label="Cayman Islands">Cayman Islands</option>'+
                      '<option value="cl" label="Chile">Chile</option>'+
                      '<option value="co" label="Colombia">Colombia</option>'+
                      '<option value="cr" label="Costa Rica">Costa Rica</option>'+
                      '<option value="cu" label="Cuba">Cuba</option>'+
                      '<option value="dm" label="Dominica">Dominica</option>'+
                      '<option value="do" label="Dominican Republic">Dominican Republic</option>'+
                      '<option value="ec" label="Ecuador">Ecuador</option>'+
                      '<option value="sv" label="El Salvador">El Salvador</option>'+
                      '<option value="fk" label="Falkland Islands">Falkland Islands</option>'+
                      '<option value="gf" label="French Guiana">French Guiana</option>'+
                      '<option value="gl" label="Greenland">Greenland</option>'+
                      '<option value="gd" label="Grenada">Grenada</option>'+
                      '<option value="gp" label="Guadeloupe">Guadeloupe</option>'+
                      '<option value="gt" label="Guatemala">Guatemala</option>'+
                      '<option value="gy" label="Guyana">Guyana</option>'+
                      '<option value="ht" label="Haiti">Haiti</option>'+
                      '<option value="hn" label="Honduras">Honduras</option>'+
                      '<option value="jm" label="Jamaica">Jamaica</option>'+
                      '<option value="mq" label="Martinique">Martinique</option>'+
                      '<option value="mx" label="Mexico">Mexico</option>'+
                      '<option value="ms" label="Montserrat">Montserrat</option>'+
                      '<option value="an" label="Netherlands Antilles">Netherlands Antilles</option>'+
                      '<option value="ni" label="Nicaragua">Nicaragua</option>'+
                      '<option value="pa" label="Panama">Panama</option>'+
                      '<option value="py" label="Paraguay">Paraguay</option>'+
                      '<option value="pe" label="Peru">Peru</option>'+
                      '<option value="pr" label="Puerto Rico">Puerto Rico</option>'+
                      '<option value="bl" label="Saint Barthélemy">Saint Barthélemy</option>'+
                      '<option value="kn" label="Saint Kitts and Nevis">Saint Kitts and Nevis</option>'+
                      '<option value="lc" label="Saint Lucia">Saint Lucia</option>'+
                      '<option value="mf" label="Saint Martin">Saint Martin</option>'+
                      '<option value="pm" label="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>'+
                      '<option value="vc" label="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>'+
                      '<option value="sr" label="Suriname">Suriname</option>'+
                      '<option value="tt" label="Trinidad and Tobago">Trinidad and Tobago</option>'+
                      '<option value="tc" label="Turks and Caicos Islands">Turks and Caicos Islands</option>'+
                      '<option value="vi" label="U.S. Virgin Islands">U.S. Virgin Islands</option>'+
                      '<option value="us" label="United States">United States</option>'+
                      '<option value="uy" label="Uruguay">Uruguay</option>'+
                      '<option value="ve" label="Venezuela">Venezuela</option>'+
                    '</optgroup>'+
                    '<optgroup id="country-optgroup-Asia" label="Asia">'+
                      '<option value="af" label="Afghanistan">Afghanistan</option>'+
                      '<option value="am" label="Armenia">Armenia</option>'+
                      '<option value="az" label="Azerbaijan">Azerbaijan</option>'+
                      '<option value="bh" label="Bahrain">Bahrain</option>'+
                      '<option value="bd" label="Bangladesh">Bangladesh</option>'+
                      '<option value="bt" label="Bhutan">Bhutan</option>'+
                      '<option value="bn" label="Brunei">Brunei</option>'+
                      '<option value="kh" label="Cambodia">Cambodia</option>'+
                      '<option value="cn" label="China">China</option>'+
                      '<option value="cy" label="Cyprus">Cyprus</option>'+
                      '<option value="ge" label="Georgia">Georgia</option>'+
                      '<option value="hk" label="Hong Kong SAR China">Hong Kong SAR China</option>'+
                      '<option value="in" label="India">India</option>'+
                      '<option value="id" label="Indonesia">Indonesia</option>'+
                      '<option value="ir" label="Iran">Iran</option>'+
                      '<option value="iq" label="Iraq">Iraq</option>'+
                      '<option value="il" label="Israel">Israel</option>'+
                      '<option value="jp" label="Japan">Japan</option>'+
                      '<option value="jo" label="Jordan">Jordan</option>'+
                      '<option value="kz" label="Kazakhstan">Kazakhstan</option>'+
                      '<option value="kw" label="Kuwait">Kuwait</option>'+
                      '<option value="kg" label="Kyrgyzstan">Kyrgyzstan</option>'+
                      '<option value="la" label="Laos">Laos</option>'+
                      '<option value="lb" label="Lebanon">Lebanon</option>'+
                      '<option value="mo" label="Macau SAR China">Macau SAR China</option>'+
                      '<option value="my" label="Malaysia">Malaysia</option>'+
                      '<option value="mv" label="Maldives">Maldives</option>'+
                      '<option value="mn" label="Mongolia">Mongolia</option>'+
                      '<option value="mm" label="Myanmar [Burma]">Myanmar [Burma]</option>'+
                      '<option value="np" label="Nepal">Nepal</option>'+
                      '<option value="nt" label="Neutral Zone">Neutral Zone</option>'+
                      '<option value="kp" label="North Korea">North Korea</option>'+
                      '<option value="om" label="Oman">Oman</option>'+
                      '<option value="pk" label="Pakistan">Pakistan</option>'+
                      '<option value="ps" label="Palestinian Territories">Palestinian Territories</option>'+
                      '<option value="yd" label="People\'s Democratic Republic of Yemen">People\'s Democratic Republic of Yemen</option>'+
                      '<option value="ph" label="Philippines">Philippines</option>'+
                      '<option value="qa" label="Qatar">Qatar</option>'+
                      '<option value="sa" label="Saudi Arabia">Saudi Arabia</option>'+
                      '<option value="sg" label="Singapore">Singapore</option>'+
                      '<option value="kr" label="South Korea">South Korea</option>'+
                      '<option value="lk" label="Sri Lanka">Sri Lanka</option>'+
                      '<option value="sy" label="Syria">Syria</option>'+
                      '<option value="tw" label="Taiwan">Taiwan</option>'+
                      '<option value="tj" label="Tajikistan">Tajikistan</option>'+
                      '<option value="th" label="Thailand">Thailand</option>'+
                      '<option value="tl" label="Timor-Leste">Timor-Leste</option>'+
                      '<option value="tr" label="Turkey">Turkey</option>'+
                      '<option value="tm" label="Turkmenistan">Turkmenistan</option>'+
                      '<option value="ae" label="United Arab Emirates">United Arab Emirates</option>'+
                      '<option value="uz" label="Uzbekistan">Uzbekistan</option>'+
                      '<option value="vn" label="Vietnam">Vietnam</option>'+
                      '<option value="ye" label="Yemen">Yemen</option>'+
                    '</optgroup>'+
                    
                    '<optgroup id="country-optgroup-Oceania" label="Oceania">'+
                      '<option value="as" label="American Samoa">American Samoa</option>'+
                      '<option value="aq" label="Antarctica">Antarctica</option>'+
                      '<option value="au" label="Australia">Australia</option>'+
                      '<option value="bv" label="Bouvet Island">Bouvet Island</option>'+
                      '<option value="io" label="British Indian Ocean Territory">British Indian Ocean Territory</option>'+
                      '<option value="cx" label="Christmas Island">Christmas Island</option>'+
                      '<option value="cc" label="Cocos [Keeling] Islands">Cocos [Keeling] Islands</option>'+
                      '<option value="ck" label="Cook Islands">Cook Islands</option>'+
                      '<option value="fj" label="Fiji">Fiji</option>'+
                      '<option value="pf" label="French Polynesia">French Polynesia</option>'+
                      '<option value="tf" label="French Southern Territories">French Southern Territories</option>'+
                      '<option value="gu" label="Guam">Guam</option>'+
                      '<option value="hm" label="Heard Island and McDonald Islands">Heard Island and McDonald Islands</option>'+
                      '<option value="ki" label="Kiribati">Kiribati</option>'+
                      '<option value="mh" label="Marshall Islands">Marshall Islands</option>'+
                      '<option value="fm" label="Micronesia">Micronesia</option>'+
                      '<option value="nr" label="Nauru">Nauru</option>'+
                      '<option value="nc" label="New Caledonia">New Caledonia</option>'+
                      '<option value="nz" label="New Zealand">New Zealand</option>'+
                      '<option value="nu" label="Niue">Niue</option>'+
                      '<option value="nf" label="Norfolk Island">Norfolk Island</option>'+
                      '<option value="mp" label="Northern Mariana Islands">Northern Mariana Islands</option>'+
                      '<option value="pw" label="Palau">Palau</option>'+
                      '<option value="pg" label="Papua New Guinea">Papua New Guinea</option>'+
                      '<option value="pn" label="Pitcairn Islands">Pitcairn Islands</option>'+
                      '<option value="ws" label="Samoa">Samoa</option>'+
                      '<option value="sb" label="Solomon Islands">Solomon Islands</option>'+
                      '<option value="gs" label="South Georgia and the South Sandwich Islands">South Georgia and the South Sandwich Islands</option>'+
                      '<option value="tk" label="Tokelau">Tokelau</option>'+
                      '<option value="to" label="Tonga">Tonga</option>'+
                      '<option value="tv" label="Tuvalu">Tuvalu</option>'+
                      '<option value="um" label="U.S. Minor Outlying Islands">U.S. Minor Outlying Islands</option>'+
                      '<option value="vu" label="Vanuatu">Vanuatu</option>'+
                      '<option value="wf" label="Wallis and Futuna">Wallis and Futuna</option>'+
                    '</optgroup>'+
                  '</select>',
      scope: {
        model: '=?'
      },
      link: function(scope, element, attributes) {
        scope.lang   = $rootScope.lang;
      }
    };
  }
]);