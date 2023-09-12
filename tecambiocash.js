var tecambiocash = (() => {
    const baseUrl = "https://system.tecambiocash.com/api/"; 
    const baseRedirect = "https://tecambiocash.com/";

    let auth = {
        verify(valid_roles) {
            let role = auth.role();

            if(window.localStorage.token == undefined || !valid_roles.includes(role)) {
                window.location.replace("pages-sign-in.html");
                return "REDIRECT";               
            }

            return role;
        },
        isLog() {
            return window.localStorage.token != undefined;
        },
        getToken() {
            return "Bearer " + window.localStorage.getItem('token');
        },
        user() {
            return window.localStorage.getItem('user') ? JSON.parse(window.localStorage.getItem('user')) : {};
        },
        updateUser(callback) {
            let url = baseUrl + "user/current";
            fetch(url, {
                headers: {
                    'Authorization': auth.getToken()
                }
            }).then(res => res.json()).then(json => {
                window.localStorage.setItem('user', JSON.stringify(json.data));
                callback();
            });
        },
        role() {
            return window.localStorage.getItem('role');
        }
    };

    let dropdunmng = {
        init(ids, callback = () => {}, initial = null, only = null) {
            let url = baseUrl + "currency_value";
        	//Obtiene los nombres de las divisas
            fetch(url).then(res => res.json()).then((data) => {
                
                let html = "";
                for(let e of data) {

                    if(only != null && !only.includes(e.name)) continue;

                    let name = e.name;  
                    html += "<img src='https://tecambiocash.com/images/" + name + ".jpg' width='60' height='40' data-name='" + name + "'>";
                }
                
                ids.forEach((id, index) => {
                    let el_initial = initial != null ? initial[index] : "Paypal";
                    let el = document.getElementById(id);
                    el.innerHTML = html;
                    let selected = $(el).find("img[data-name=" + el_initial + "]");
                    selected.addClass('dropdun-selected');
                    selected.detach().prependTo(el);
                });
                callback();
                dropdunmng.addListeners();
            });
        },
        addListeners() {
            let dropdunOpen = true, dropdun = $('.dropdun');
            dropdun.on('click', (el) => {
                dropdunOpen = !dropdunOpen;
                
                if(dropdunOpen) dropdunmng.closeDropdun();
                else dropdunmng.openDropdun(el);
            });

            $('.toggle-dropdun').on('click', (el) => {
                el.preventDefault();
                dropdunOpen = !dropdunOpen;
                
                el.currentTarget = $(el.currentTarget).parent().find('.dropdun');

                if(dropdunOpen) dropdunmng.closeDropdun();
                else dropdunmng.openDropdun(el);
            });
            
            let selectables = $('.dropdun img');
            selectables.on('click', (el) => {
                let current = $(el.currentTarget),
                    parent = current.parent();
                parent.find('.dropdun-selected').removeClass('dropdun-selected');
                current.addClass('dropdun-selected');
                current.detach().prependTo(parent);
            });
        },
        openDropdun(el) {
	        let dropdun = $('.dropdun');
	        dropdun.css('z-index', '1');
            dropdun.find('.dropdun-inner').css('z-index', '1');
            dropdun.find('img').css('z-index', '1');
            let current = $(el.currentTarget);
            current.css('overflow', 'visible');
            current.css('z-index', '100');
            current.find('.dropdun-inner').css('z-index', 100);
            current.find('img').css('z-index', '100');
	    },
	    closeDropdun() {
	        let dropdun = $('.dropdun');
	        dropdun.css('z-index', '100');
	        dropdun.find('img').css('z-index', 100);
            dropdun.find('.dropdun-inner').css('z-index', 100);
            dropdun.find('.dropdun-inner').scrollTop(0);
	        dropdun.css('overflow', 'hidden');
	    },
        getValue(id) {
            return $('#' + id + ' img:first-child').attr('data-name');
        }
    }

    let calculatormng = {
        init() {
            dropdunmng.init(['currency1', 'currency2'], () => calculatormng.addListeners(), ["Paypal", "Bancolombia"]);
        },
        addListeners() {
            let button = document.getElementById('btnConvert'),
	            cambiar = document.getElementById('cambiar');
            
            button.addEventListener('click', (ev) => {
                ev.preventDefault();
                calculatormng.calc_result();
            });
            
            cambiar.addEventListener('click', (ev) => {
                ev.preventDefault();
                
                let select1 = $('#currency1'),
    	            select2 = $('#currency2'),
                    currency_data = document.getElementById('currency-data'),
                    currency_result = document.getElementById('currency-result');
                
                //Swap numbers
                let s = currency_data.value;
                currency_data.value = currency_result.value;
                currency_result.value = s;
                
                //Swap currency
                let first = select1.find('.dropdun-selected').attr('src'),
                    second = select2.find('.dropdun-selected').attr('src');
                
                $('.dropdun-selected').removeClass('dropdun-selected');
                select1.find('img[src="' + second + '"]').detach().prependTo(select1).addClass('dropdun-selected');
                select2.find('img[src="' + first + '"]').detach().prependTo(select2).addClass('dropdun-selected');
            });
        },
        calc_result() {
	        let select1 = dropdunmng.getValue('currency1'),
	            select2 = dropdunmng.getValue('currency2'),
                currency_data = document.getElementById('currency-data').value,
                currency_result = document.getElementById('currency-result');
                
            let url = baseUrl + "currency_value/" + select1 + "/" + select2 + "/" + currency_data;
            fetch(url).then((res) => res.json()).then( data => {
                currency_result.value = data.data.total;   
            });
         
	    }
    };

    let accountmng = {
        init() {
            let btnAccount = document.querySelector('#add-account');
            btnAccount.addEventListener('click', (ev) => {
                ev.preventDefault();
                let currency = dropdunmng.getValue('currency');
                let html = `
                    <div class='account'>
                        <input type='text' disabled value='${currency}' name='account-name'>
                        <input type='text' name='identificator'>
                        <button class='delete-account'>X</button>
                    </div>
                `;

                let list = document.querySelector('#account-list');
                list.insertAdjacentHTML('beforeend', html);
                accountmng.listListeners();
            });
        },
        listListeners() {
            let deleters = document.querySelectorAll('.delete-account');

            deleters.forEach(deleter => {
                deleter.removeEventListener('click', accountmng.deleteAccount);
                deleter.addEventListener('click', accountmng.deleteAccount);
            });

        },
        deleteAccount(ev) {
            ev.preventDefault();
            let target = ev.target;
            let account = target.parentElement;
            account.remove();
        }
    };

    let convertmng = {
        init(input, from, to, callback) {
            let el = document.querySelector(input);

            el.addEventListener('keyup', ev => convertmng.do(el, from, to, callback));
            document.querySelectorAll('.dropdun-inner').forEach(drop => drop.addEventListener('click', ev => convertmng.do(el, from, to, callback)));
        },
        do(el, from, to, callback) {
            if(el.value == undefined || el.value == 0 || el.value == '') return;
 
            let new_from = from[0] == "#" ? dropdunmng.getValue(from.substring(1)) : from;
            let new_to = to[0] == "#" ? dropdunmng.getValue(to.substring(1)) : to;
            let url = baseUrl + "currency_value/" + new_from + "/" + new_to + "/" + el.value;
            fetch(url).then((res) => res.json()).then(json => callback(json.data));
        }
    };

    let modals = {
        init() {
            
        }
    }

	let index = {
		init() {
			calculatormng.init();

            let btn = document.querySelector("#doTransaction");

            btn.addEventListener('click', (ev) => {
                let amount = document.querySelector('#currency-data').value,
                    currency_from = dropdunmng.getValue('currency1'),
                    currency_to = dropdunmng.getValue('currency2'),
                    type = "INTERCAMBIAR";
                
                let url = baseUrl + "transaction";
                let data = JSON.stringify({ amount, currency_from, currency_to, type });

                if(!auth.isLog()) {
                    window.localStorage.setItem('redirect', 'create-transaction');
                    window.localStorage.setItem('transaction', data);
                    window.location.assign("tecambiocash%20sistema%20de%20info/static/pages-sign-in.html");
                    return;
                }

                fetch(url, {
                    body: data,
                    method: "POST",
                    headers: {
                        "Authorization": auth.getToken(),
                        'Content-Type': 'application/json'
                    }
                }).then(res => res.json()).then(json => {
                    window.location.replace(baseRedirect + "wallet");
                }); 

            });
		}		
	};

    let login = {
        init() {
            let btn = document.getElementById('btnSign');
        
            btn.addEventListener('click', (ev) => {
                ev.preventDefault();
                
                let email = document.getElementById('email').value,
                    password = document.getElementById('password').value;
                    
                let url = baseUrl + "user/login";
                let data = {email, password};
                fetch(url, {
                    method: "POST",
                    body: JSON.stringify(data),
                    headers: {
                        "Content-Type": "application/json"
                    }
                }).then((data) => {
                    
                    if(data.status == 404) {
                        document.getElementById('error').classList.remove('d-none');
                    } else if(data.status == 200) {
                        return data.json();
                    }
                    
                }).then((user) => {
                    window.localStorage.setItem('user', JSON.stringify(user.data.user));
                    window.localStorage.setItem('token', user.data.token);
                    window.localStorage.setItem('role', user.data.user.role);
                    
                    if(window.localStorage.role == 'ADMIN')
                        window.location.replace(baseRedirect + "tecambiocash%20sistema%20de%20info/static/index.html");
                    else if(window.localStorage.role == 'CAJERO')
                        window.location.replace(baseRedirect + "tecambiocash%20sistema%20de%20info/static/index.html");
                    else
                        window.location.replace(baseRedirect + "wallet");
                        
                });
            });
        }
    };
    
    let register = {
    	init() {
    		let form = document.querySelector("#register-form");
        
        	form.addEventListener('submit', (ev) => {
            	ev.preventDefault();
                
                if(form.elements.r_password.value != form.elements.password.value)
                    return alert("Las contraseñas no concuerdan");

                let data = new FormData(form);

                let account_names = document.getElementsByName('account-name');
                let identificators = document.getElementsByName('identificator');

                for(let i = 0; i < account_names.length; i++) {
                    data.append('account_name', account_names[i].value);
                    data.append('identificator', identificators[i].value);
                }

            	let url = baseUrl + "user/create";
                fetch(url, {
                	method: "POST",
                    body: data
            	}).then((data) => {
                	
                	if(data.status == 404) {
                    	document.getElementById('error').classList.remove('d-none');
                	} else if(data.status == 200) {
                    	return data.json();
                	}
                	
            	}).then((result) => {
                	window.location.replace(baseRedirect + "tecambiocash%20sistema%20de%20info/static/pages-sign-in.html");
            	});
        	});

            dropdunmng.init(['currency'], () => {});
            accountmng.init();
    	}
    };
    
    let divisas = {
        init() {
            auth.verify(["ADMIN"]);

        	let url = baseUrl + "currency_value";
        	//Obtiene los nombres de las divisas
        	fetch(url).then(res => res.json()).then((data) => {
            
            	let html = "<option value=''></option>";
            	for(let e of data) {
                	let name = e.name;
                	html += "<option value='" + name + "'>" + name + "</option>";
            	}
            	
            	document.getElementById('currency').innerHTML = html;
            	divisas.addListeners();
        	});

            divisas.addConfigurations();
        },
        addListeners() {
            let select = document.getElementById('currency'),
                value = document.getElementById('value'),
                button = document.getElementById('save');
            
            select.addEventListener('change', (ev) => {
               let selected = ev.target.value;
               
               let url = baseUrl + "currency_value/" + selected;
               fetch(url).then((res) => res.json()).then((data) => {
                   value.value = data.value;
               });
               
            });
            
            button.addEventListener('click', (ev) => {
                ev.preventDefault();
                let new_value = value.value;
                let selected = select.value;
                let url = baseUrl + "currency_value/" + selected;
                let data = {value: new_value};
                fetch(url, {
                    headers: { "Content-Type": "application/json", "Authorization": auth.getToken() },
                    method: "PUT",
                    body: JSON.stringify(data)
                }).then(res => res.json()).then((data) => {
                   let mes = document.getElementById('alert');
                   mes.classList.remove("d-none");
                   
                   setTimeout(() => mes.classList.add('d-none'), 4000);
                   
                });        
                
            });
            
        },
        addConfigurations() {
            let form = document.querySelector('#comission_form');

            let url = baseUrl + 'config';
            fetch(url, {
                headers: {
                    "Authorization": auth.getToken(),
                    "Accept": "application/json"
                }
            }).then(res => res.json()).then(json => {
                form.elements.comission_in.value = json.data.comission_in;
                form.elements.comission_out.value = json.data.comission_out;
                form.elements.comission_exchange.value = json.data.comission_exchange;
                form.elements.comission_cashier.value = json.data.comission_cashier;

                form.addEventListener('submit', (ev) => {
                    ev.preventDefault();
                    let data = {
                        'comission_in': form.elements.comission_in.value,
                        'comission_out': form.elements.comission_out.value,
                        'comission_exchange': form.elements.comission_exchange.value,
                        'comission_cashier': form.elements.comission_cashier.value,
                    };

                    fetch(url, {
                        method: "PUT",
                        body: JSON.stringify(data),
                        headers: {
                            "Authorization": auth.getToken(),
                            "Content-Type": "application/json"
                        }
                    }).then(res => res.json()).then(json => {
                        let mes = document.getElementById('alert');
                        mes.classList.remove("d-none");
                        
                        setTimeout(() => mes.classList.add('d-none'), 4000);
                    });

                });

            });
        }
    };
	
    let addCashier = {
        init() {
            auth.verify(["ADMIN"]);

            let form = document.querySelector('#crear-cajero');
        
            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                let name = form.elements.name.value,
                    password = form.elements.password.value,
                    email = form.elements.email.value,
                    c_password = form.elements.c_password.value;

                let account_names = document.getElementsByName('account-name');
                let identificators = document.getElementsByName('identificator');

                let account_name = [],
                    identificator = [];
                for(let i = 0; i < account_names.length; i++) {
                    account_name.push(account_names[i].value);
                    identificator.push(identificators[i].value);
                }

                let url = baseUrl + "user/cashier/create";
                let data = {email, name, password, account_name, identificator};
                if(password != c_password) {
                    alert("Las contraseñas no coinciden");
                    return;
                }

                fetch(url, {
                    headers: {"Content-Type": "application/json", "Authorization": auth.getToken()},
                    body: JSON.stringify(data),
                    method: "POST"
                }).then(res => res.json()).then((data) => {
                    location.replace(baseRedirect + "tecambiocash%20sistema%20de%20info/static/ui-buttons.html");
                });        

            });

            dropdunmng.init(['currency'], () => {});
            accountmng.init();
        }
    };

    let cajeros = {
        init() {
            auth.verify(["ADMIN"]);

            this.getCashiers();            
            this.getCustomers();
            this.getApplications();
        },
        getCashiers() {
            let table = document.querySelector('#cashier-list tbody');
            let url = baseUrl + "user/cashier";
            let options = {
                headers: {
                    "Authorization": auth.getToken(),
                    "Accept": "application/json"
                }
            };

            fetch(url, options).then(data => data.json()).then(json => {

                let html = "";
                for (const cashier of json) {
                    let last_action = `<input type='number' class='saldo form-control' style='max-width: 60px; margin-right: 5px; display: inline-block;' value='${cashier.balance}'><button class='btn btn-primary cambiar_saldo' user-data='${cashier.id}'>Cambiar saldo</button>`;
                    
                    html += "<tr>";
                    html += "<td scope='row'>" + cashier.name + "</td>";
                    html += "<td>" + cashier.email + "</td>";
                    html += "<td>" + last_action + "</td>";
                    html += "</tr>";
                }

                table.innerHTML = html;
            
                let saldo = document.querySelectorAll('.cambiar_saldo');
                saldo.forEach((el) => el.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    let id = ev.target.getAttribute('user-data');
                    let saldoUrl = baseUrl + "user/" + id + "/balance/" + el.previousElementSibling.value;

                    fetch(saldoUrl, {
                        method: "PUT",
                        headers: {
                            'Authorization': auth.getToken()
                        }
                    }).then(res => res.json()).then(data => {
                        alert("Valor de saldo cambiado correctamente");
                    });
                }));
            });
        },
        getCustomers() {
            let table = document.querySelector('#customer-list tbody');
            let url = baseUrl + "user/customer";
            let options = {
                headers: {
                    'Authorization': auth.getToken(),
                    "Accept": "application/json"
                }
            };

            fetch(url, options).then(data => data.json()).then(json => {

                let html = "";
                for (const customer of json) {
                    const doc_url = baseUrl + "user/" + customer.id + "/doc-image";
                    const img_url = baseUrl + "user/" + customer.id + "/image";

                    let last_action = customer.state == "ACTIVE" ?
                    `<input type='number' class='prestacash form-control' style='max-width: 60px; margin-right: 5px; display: inline-block;' value='${customer.prestacash}'><button class='btn btn-primary cambiar_prestacash' user-data='${customer.id}'>Cambiar prestacash</button>`:
                    `<a class='enableUser' user-data='${customer.id}'>Activar Usuario</a>`; 

                    html += "<tr>";
                    html += "<td scope='row'>" + customer.name + "</td>";
                    html += "<td>" + customer.email + "</td>";
                    html += `<td>
                                <a href="#" onclick='tecambiocash.cajeros.viewFile(\"${img_url}\")'>Ver imagen</a>
                                <a href="#" onclick='tecambiocash.cajeros.viewFile(\"${doc_url}\")'>Ver documento</a>
                                ${last_action}
                            </td>`;
                    html += "</tr>";
                }

                table.innerHTML = html;
                
                let enableUser = document.querySelector('.enableUser');
                if(enableUser) {
                    enableUser.addEventListener('click', (ev) => {
                        let id = ev.target.getAttribute('user-data');
                        let enableUrl = baseUrl + "user/" + id + "/enable";
    
                        fetch(enableUrl, {
                            method: "PUT",
                            headers: {
                                'Authorization': auth.getToken()
                            }
                        }).then(res => res.json()).then(data => {
                            alert("Usuario activado correctamente");
                        });
    
                    });
                
                }

                let prestacash = document.querySelectorAll('.cambiar_prestacash');
                prestacash.forEach((el) => el.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    let id = ev.target.getAttribute('user-data');
                    let prestaUrl = baseUrl + "user/" + id + "/prestacash/" + el.previousElementSibling.value;

                    fetch(prestaUrl, {
                        method: "PUT",
                        headers: {
                            'Authorization': auth.getToken()
                        }
                    }).then(res => res.json()).then(data => {
                        alert("Valor de prestacash cambiado correctamente");
                    });
                }));

            })
        },
        getApplications() {
            let table = document.querySelector('#application-list tbody');
            let url = baseUrl + "application";
            let options = {
                headers: {
                    "Authorization": auth.getToken(),
                    "Accept": "application/json"
                }
            };

            fetch(url, options).then(data => data.json()).then(json => {

                let html = "";
                for (const application of json.data) {
                    html += "<tr>";
                    html += "<td scope='row'>" + application.user.email + "</td>";
                    html += "<td>" + application.name1+ "</td>";
                    html += "<td>" + application.lastname1+ "</td>";
                    html += "<td>" + application.phone1+ "</td>";
                    html += "<td>" + application.email1+ "</td>";
                    html += "<td>" + application.name1+ "</td>";
                    html += "<td>" + application.relationship1+ "</td>";
                    html += "<td>" + application.name2+ "</td>";
                    html += "<td>" + application.lastname2+ "</td>";
                    html += "<td>" + application.phone2+ "</td>";
                    html += "<td>" + application.email2+ "</td>";
                    html += "<td>" + application.name2+ "</td>";
                    html += "<td>" + application.relationship2+ "</td>";
                    html += "<td></td>";
                    html += "</tr>";
                }

                table.innerHTML = html;
            });
        },
        viewFile: async (url) => {
            // Change this to use your HTTP client
            fetch(url, {
                headers: {
                    "Authorization": auth.getToken(),
                }
            } ) // FETCH BLOB FROM IT
                .then((response) => response.blob())
                .then((blob) => { // RETRIEVE THE BLOB AND CREATE LOCAL URL
                var _url = window.URL.createObjectURL(blob);
                window.open(_url, "_blank").focus(); // window.open + focus
            }).catch((err) => {
                console.log(err);
            });
        }
    }

    let services = {
        init() {
            auth.verify(["ADMIN", "CAJERO"]);
            this.getProcess();
            this.getWaiting();
            this.getCompleted();
        },
        getWaiting() {
            let table = document.querySelector('#waiting-list tbody');
            let url = baseUrl + "transaction/waiting";
            let options = {
                headers: {
                    "Authorization": auth.getToken(),
                    "Accept": "application/json"
                }
            };

            fetch(url, options).then(data => data.json()).then(json => {

                let html = "";
                for (const transaction of json.data) {
                    html += "<tr>";
                    html += "<td scope='row'>" + transaction.type + "</td>";
                    html += "<td>" + transaction.currency.name + "</td>";
                    html += "<td>" + transaction.amount + (transaction.type == 'INTERCAMBIAR' ? " &rarr; " + transaction.currencyTo.name : "") +  "</td>";
                    html += "<td><button class='take btn btn-primary' transaction='" + transaction.id + "'>Tomar</button></td>";
                    html += "</tr>";
                }

                table.innerHTML = html;

                let take = document.querySelectorAll('.take').forEach((t) => {
                    t.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        let id = ev.currentTarget.getAttribute('transaction');
                        let options = {
                            method: "PUT",
                            headers: {
                                "Authorization": auth.getToken()
                            }
                        };
    
                        let url = baseUrl + "transaction/" + id + "/take";
                        fetch(url, options).then(data => data.json()).then((r) => {
                            location.reload();
                        });
    
                    })
                });
                
            });
        },
        getCompleted() {
            let table = document.querySelector('#complete-list tbody');
            let url = baseUrl + "transaction";
            let options = {
                headers: {
                    "Authorization": auth.getToken(),
                    "Accept": "application/json"
                }
            };

            fetch(url, options).then(data => data.json()).then(json => {
                let html = "";
                for (const transaction of json.data) {
                    html += "<tr>";
                    html += "<td scope='row'>" + transaction.type + "</td>";
                    html += "<td>" + transaction.currency.name + (transaction.type == 'INTERCAMBIAR' ? " &rarr; " + transaction.currencyTo.name : "") + "</td>";
                    html += "<td>" + transaction.amount + "</td>";
                    html += "<td></td>";
                    html += "</tr>";
                }

                table.innerHTML = html;
            });
        },
        getProcess() {
            let table = document.querySelector('#process-list tbody');
            let url = baseUrl + "transaction/process";
            let options = {
                headers: {
                    "Authorization": auth.getToken(),
                    "Accept": "application/json"
                }
            };

            fetch(url, options).then(data => data.json()).then(json => {

                let html = "";
                for (const transaction of json.data) {
                    html += "<tr>";
                    html += "<td scope='row'>" + transaction.type + "</td>";
                    html += "<td>" + transaction.currency.name + (transaction.type == 'INTERCAMBIAR' ? " &rarr; " + transaction.currencyTo.name : "") + "</td>";
                    html += "<td>" + transaction.amount + "</td>";
                    html += "<td>" + transaction.status + "</td>";
                    html += "<td><button class='voucher btn btn-primary me-2' transaction='" + transaction.id + "'>Comprobante</button>&nbsp;&nbsp;<button class='complete btn btn-primary me-2' transaction='" + transaction.id + "'>Completar</button><button class='btn btn-primary show-accounts' data-id='" + transaction.user_from + "'>Ver cuentas bancarias</button></td>";
                    html += "</tr>";
                }

                table.innerHTML = html;

                let complete = document.querySelectorAll('.complete').forEach((c) => {
                    c.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        let id = ev.currentTarget.getAttribute('transaction');
                        let options = {
                            method: "PUT",
                            headers: {
                                "Authorization": auth.getToken()
                            }
                        };
    
                        let url = baseUrl + "transaction/" + id + "/complete";
                        fetch(url, options).then(data => data.json()).then((r) => {
                            location.reload();
                        });
    
                    })
                });

                let voucher = document.querySelectorAll('.voucher').forEach((c) => {
                    c.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        let id = ev.currentTarget.getAttribute('transaction');
                        let options = {
                            headers: {
                                "Authorization": auth.getToken()
                            }
                        };
    
                        let url = baseUrl + "transaction/" + id + "/voucher-user";
                        fetch(url, options).then(data => data.blob()).then((b) => {
                            let image = new Image();
                            image.src = URL.createObjectURL(b);
                            
                            let el = document.querySelector('#voucher');
                            el.innerHTML = "<h5>Comprobante de pago</h5>";
                            el.append(image);
                        });
    
                    })
                });

                document.querySelectorAll(".show-accounts").forEach((el) => el.addEventListener('click', (ev) => {
                    let id = el.getAttribute('data-id');

                    let accountUrl = baseUrl + "account/user/" + id;
                    fetch(accountUrl, options).then(res => res.json()).then(json => {
                        let accounts = document.querySelector("#accounts");
                        let html = "<h2 class='title-1 m-b-25'>Cuentas bancarias</h2><p class='mb-25'>Para completar la transaccion transfiera a cualquiera de estas cuentas</p><div id='mis_cuentas'>";

                        for(const acc of json.data) {
                            html += `
                                <div class='account-card'>
                                    <img src='${baseRedirect}/images/${acc.currency.name}.jpg'/>
                                    <div class='account-data'>
                                        <div class='account-title'>${acc.currency.name}</div>
                                        <div class='account-identificator'>${acc.identificator}</div>
                                    </div>
                                </div>
                            `;
                        }

                        html += "</div>";
                        html += "<form enctype='multipart/form-data' method='post' id='comprobante-form' class='account-actions'><input type='file' id='comprobante' name='voucher'><button type='submit' class='btn btn-primary' id='pay'>Enviar comprobante</button></form>";
                        accounts.innerHTML = html;

                        let comprobante_form = document.querySelector("#comprobante-form");
                        comprobante_form.addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            let comprobanteUrl = baseUrl + 'transaction/' + id + '/voucher-user';

                            fetch(comprobanteUrl, {
                                method: "POST",
                                body: new FormData(comprobante_form),
                                headers: {
                                    "Authorization": auth.getToken(),
                                }
                            }).then(res => res.json()).then(json => {
                                alert("Voucher enviado correctamente");
                            });

                        });

                    });


                }));

            });

        },
        
    };

    let banks = {
        currentUser: null,
        init() {
            auth.verify(["ADMIN", "CAJERO"]);
            
            if(auth.role() != "ADMIN")
                document.querySelector("#editar_cuentas").style.display = "none";

            banks.showMyBanks();
            banks.addListeners();
            dropdunmng.init(['currency'], () => {});
        },
        addListeners() {
            let form = document.querySelector("#search_user");

            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                let email = form.elements.email.value;

                let url = baseUrl + "user/email/" + email;
                fetch(url, {
                    headers: {
                        'Authorization': auth.getToken()
                    }
                }).then(res => res.json()).then(data => {
                    let user = data.data;

                    document.querySelector("#user_name").innerText = user.name;
                    document.querySelector("#user_email").innerText = user.email;
                    document.querySelector("#user_role").innerText = user.role;
                    banks.currentUser = user;
                });

                document.querySelector("#mis_cuentas").innerHTML = "";
                banks.showUserBanks(email);
            });

            let create = document.querySelector("#agregar_cuenta");

            create.addEventListener('submit', (ev) => {
                ev.preventDefault();
                let url = baseUrl + "account";
                let data = {
                    user_id: banks.currentUser.id,
                    currency: dropdunmng.getValue('currency'),
                    identificator: create.elements.identificator.value
                };
                
                fetch(url, {
                    method: "POST",
                    body: JSON.stringify(data),
                    headers: {
                        'Authorization': auth.getToken(),
                        'Content-Type': "application/json"
                    }
                }).then(res => res.json()).then(data => {
                    document.querySelector("#mis_cuentas").innerHTML = "";
                    banks.showUserBanks(banks.currentUser.email); 
                });
            });

        },
        showUserBanks(email) {
            let container = document.querySelector('#mis_cuentas');

            let url = baseUrl + "account/email/" + email;
            fetch(url, {
                headers: {
                    'Authorization': auth.getToken()
                }
            }).then(res => res.json()).then(data => {
                let html = "";

                for (const account of data.data) {
                    html += `
                        <div class='account-card'>
                            <div class='account-delete' data-id='${account.id}'>
                                X
                            </div>
                            <img src='${baseRedirect}/images/${account.currency.name}.jpg'/>
                            <div class='account-data'>
                                <div class='account-title'>${account.currency.name}</div>
                                <div class='account-identificator'>${account.identificator}</div>
                            </div>
                        </div>
                    `;
                }

                container.innerHTML = html;
                let deleters = document.querySelectorAll('.account-delete');
                deleters.forEach((deleter) => deleter.addEventListener("click", (ev) => banks.deleteBank(ev.target)));
            });
        },
        deleteBank(element) {
            let id = element.getAttribute('data-id');
            let url = baseUrl + "account/" + id;
            fetch(url, {
                method: "DELETE",
                headers: {
                    'Authorization': auth.getToken()
                }
            }).then(res => res.json()).then(data => {
            });

            element.parentElement.remove();
        },
        showMyBanks() {
            let container = document.querySelector('#mis_cuentas');

            let url = baseUrl + "account";
            fetch(url, {
                headers: {
                    'Authorization': auth.getToken()
                }
            }).then(res => res.json()).then(data => {
                let html = "";

                for (const account of data.data) {
                    html += `
                        <div class='account-card'>
                            <img src='${baseRedirect}/images/${account.currency.name}.jpg'/>
                            <div class='account-data'>
                                <div class='account-title'>${account.currency.name}</div>
                                <div class='account-identificator'>${account.identificator}</div>
                            </div>
                        </div>
                    `;

                    container.innerHTML = html;
                }

            });

        }
    };

    let wallet = {
        init() {
            auth.verify(["CAJERO", "CLIENTE"]);

            if(window.localStorage.getItem('redirect') && window.localStorage.getItem('redirect') == 'create-transaction') {
                let data = window.localStorage.getItem('transaction');

                let url = baseUrl + 'transaction';
                fetch(url, {
                    body: data,
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        "Authorization": auth.getToken()
                    }
                }).then(res => res.json()).then(json => {
                    window.localStorage.removeItem('redirect');
                    window.localStorage.removeItem('transaction');
                    window.location.reload();
                }); 
            }

            let total = document.querySelector('#total_tcs');
            total.innerText = auth.user().balance;
            let debt = document.querySelector('#total_debt');
            debt.innerText = auth.user().debt;

            wallet.getProcess();
        },
        getProcess() {
            let table = document.querySelector('#table_transactions tbody');
            let url = baseUrl + "transaction";
            let options = {
                headers: {
                    "Authorization": auth.getToken(),
                    "Accept": "application/json"
                }
            };

            fetch(url, options).then(data => data.json()).then(json => {

                let html = "";
                for (const transaction of json.data) {
                    html += "<tr>";
                    html += "<td scope='row'>" + transaction.type + "</td>";
                    html += "<td>" + transaction.currency.name + "</td>";
                    html += "<td>" + transaction.amount + "</td>";
                    html += "<td>" + transaction.status + "</td>";
                    if(transaction.status == "EN PROGRESO")
                        html += "<td><button class='btn btn-primary show-accounts' data-id='" + transaction.user_taker + "'>Ver cuentas bancarias</button></td>";
                    if(transaction.status == "PAGADA" || transaction.status == 'COMPLETADA')
                        html += "<td><button class='btn btn-primary voucher' data-id='" + transaction.id + "'>Ver comprobante</button></td>";
                    html += "</tr>";
                }

                table.innerHTML = html;

                let voucher = document.querySelectorAll('.voucher').forEach((c) => {
                    c.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        let id = ev.currentTarget.getAttribute('transaction');
                        let options = {
                            headers: {
                                "Authorization": auth.getToken()
                            }
                        };
    
                        let url = baseUrl + "transaction/" + id + "/voucher-cashier";
                        fetch(url, options).then(data => data.blob()).then((b) => {
                            let image = new Image();
                            image.src = URL.createObjectURL(b);
                            
                            let el = document.querySelector('#voucher');
                            el.innerHTML = "<h5>Comprobante de pago</h5>";
                            el.append(image);
                        });
    
                    })
                });

                document.querySelectorAll(".show-accounts").forEach((el) => el.addEventListener('click', (ev) => {
                    let id = el.getAttribute('data-id');

                    let accountUrl = baseUrl + "account/user/" + id;
                    fetch(accountUrl, options).then(res => res.json()).then(json => {
                        let accounts = document.querySelector("#accounts");
                        let html = "<h2 class='title-1 m-b-25'>Cuentas bancarias</h2><p class='mb-25'>Para completar la transaccion transfiera a cualquiera de estas cuentas</p><div id='mis_cuentas'>";

                        for(const acc of json.data) {
                            html += `
                                <div class='account-card'>
                                    <img src='${baseRedirect}/images/${acc.currency.name}.jpg'/>
                                    <div class='account-data'>
                                        <div class='account-title'>${acc.currency.name}</div>
                                        <div class='account-identificator'>${acc.identificator}</div>
                                    </div>
                                </div>
                            `;
                        }

                        html += "</div>";
                        html += "<form enctype='multipart/form-data' method='post' id='comprobante-form' class='account-actions'><input type='file' id='comprobante' name='voucher'><button type='submit' class='btn btn-primary' id='pay'>Enviar comprobante</button></form>";
                        accounts.innerHTML = html;

                        let comprobante_form = document.querySelector("#comprobante-form");
                        comprobante_form.addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            let comprobanteUrl = baseUrl + 'transaction/' + id + '/voucher-user';

                            fetch(comprobanteUrl, {
                                method: "POST",
                                body: new FormData(comprobante_form),
                                headers: {
                                    "Authorization": auth.getToken(),
                                }
                            }).then(res => res.json()).then(json => {
                                alert("Voucher enviado correctamente");
                            });

                        });

                    });


                }));
            });
        }
    }

    let agregar = {
        init() {
            auth.verify(["CAJERO", "CLIENTE"]);
            dropdunmng.init(['currency'], () => {});
            let form = document.querySelector("#agregar-form");

            let url = baseUrl + "account";
            fetch(url, {
                method: "GET",
                headers: {
                    "Authorization": auth.getToken()
                }
            }).then(res => res.json()).then(json => {
                let accounts = json.data;

                document.querySelector('.dropdun').addEventListener('click', ev => {
                    let account = accounts.find(o => o.currency.name === dropdunmng.getValue("currency"));

                    if(account != undefined) {
                        let container = document.querySelector('#selected_account');
                        container.innerHTML = `
                            <b>La cuenta asociada que tiene registrada es:</b> ${account.identificator}
                        `;
                    } else {
                        let container = document.querySelector('#selected_account');
                        container.innerHTML = `
                            <b>No tiene cuentas registradas de este tipo</b>
                        `;
                    }

                });

                document.querySelector('.dropdun').click();
                document.querySelector('.dropdun').click();
            });


            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                let data = new FormData(form);
                data.set('currency', dropdunmng.getValue('currency'));

                let url = baseUrl + "transaction";
                fetch(url, {
                    body: data,
                    method: "POST",
                    headers: {
                        "Authorization": auth.getToken()
                    }
                }).then(res => res.json()).then(json => {
                    document.querySelector('#btnSign').disabled = true;
                    document.querySelector('#completed p').innerText = "¡Genial tu solicitud de agregar $Tcs ha sido creada con exito, en unos minutos la solicitud sera completada por uno de nuestros cajeros";
                    document.querySelector('#completed').style.display = 'block';
                });

            });

            convertmng.init("#amount", "#currency", "TCS", converted => {
                document.querySelector("#net").innerText = converted.net + " TCS";
                document.querySelector("#comission").innerText = converted.comission + " TCS";
                document.querySelector("#total").innerText = converted.total + " TCS";
            });

        }
    };

    let retirar = {
        init() {
            auth.verify(["CLIENTE", "CAJERO"]);
            dropdunmng.init(['currency'], () => {});
            let form = document.querySelector("#agregar-form");

            let url = baseUrl + "account";
            fetch(url, {
                method: "GET",
                headers: {
                    "Authorization": auth.getToken()
                }
            }).then(res => res.json()).then(json => {
                let accounts = json.data;

                document.querySelector('.dropdun').addEventListener('click', ev => {
                    let account = accounts.find(o => o.currency.name === dropdunmng.getValue("currency"));

                    if(account != undefined) {
                        let container = document.querySelector('#selected_account');
                        container.innerHTML = `
                            <b>La cuenta asociada que tiene registrada es:</b> ${account.identificator}
                        `;
                    } else {
                        let container = document.querySelector('#selected_account');
                        container.innerHTML = `
                            <b>No tiene cuentas registradas de este tipo</b>
                        `;
                    }

                });

                document.querySelector('.dropdun').click();
                document.querySelector('.dropdun').click();
            });

            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                let data = new FormData(form);
                data.set('currency', dropdunmng.getValue('currency'));

                let url = baseUrl + "transaction";
                fetch(url, {
                    body: data,
                    method: "POST",
                    headers: {
                        "Authorization": auth.getToken()
                    }
                }).then(res => res.json()).then(json => {
                    document.querySelector('#btnSign').disabled = true;
                    document.querySelector('#completed p').innerText = "¡Genial tu solicitud de retirar $Tcs ha sido creada con exito, en unos minutos la solicitud sera completada por uno de nuestros cajeros";
                    document.querySelector('#completed').style.display = 'block';
                });

            });

            
            convertmng.init("#amount", "TCS", "#currency", converted => {
                document.querySelector("#net").innerText = converted.net + " " + dropdunmng.getValue("currency");
                document.querySelector("#comission").innerText = converted.comission + " " + dropdunmng.getValue("currency");
                document.querySelector("#total").innerText = converted.total + " " + dropdunmng.getValue("currency");
            });
        }
    }

    let enviar = {
        init() {
            auth.verify(["CAJERO", "CLIENTE"]);
            dropdunmng.init(['currency'], () => {}, "TCS", ["TCS"]);
            let form = document.querySelector("#enviar-form");

            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                let data = new FormData(form);

                let url = baseUrl + "transaction";
                fetch(url, {
                    body: data,
                    method: "POST",
                    headers: {
                        "Authorization": auth.getToken()
                    }
                }).then(res => res.json()).then(json => {
                    auth.updateUser(() => {
                        document.querySelector('#btnSign').disabled = true;
                        document.querySelector('#completed p').innerText = "¡Excelente! Tus $TCS han sido enviados con éxito";
                        document.querySelector('#completed').style.display = 'block';
                    });
                });

            });

        }
    };

    let cambiar = {
        init() {
            auth.verify(["CAJERO", "CLIENTE"]);
            dropdunmng.init(['currency1', 'currency2'], () => {});
            let form = document.querySelector("#cambiar-form");

            let url = baseUrl + "account";
            fetch(url, {
                method: "GET",
                headers: {
                    "Authorization": auth.getToken()
                }
            }).then(res => res.json()).then(json => {
                let accounts = json.data;

                document.querySelectorAll('.dropdun').forEach(d => d.addEventListener('click', ev => {
                    let account1 = accounts.find(o => o.currency.name === dropdunmng.getValue("currency1"));
                    let account2 = accounts.find(o => o.currency.name === dropdunmng.getValue("currency2"));
                    let html = "";
                    let container = document.querySelector('#selected_account');

                    html += account1 != undefined ?
                            "<p><b>La cuenta asociada que tiene registrada a la cuenta de pago es: " + account1.identificator + "</b></p>" :
                            "<p><b>El tipo de cuenta seleccionada en pago no está registrada</b></p>";

                    html += account2 != undefined ?
                            "<p><b>La cuenta asociada que tiene registrada a la cuenta para recibir el dinero es: " + account1.identificator + "</b></p>" :
                            "<p><b>El tipo de cuenta seleccionada para recibir el dinero no está registrada</b></p>";

                    container.innerHTML = html;
                }));

                document.querySelectorAll('.dropdun').forEach(d => d.click() | d.click());
            });

            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                let data = new FormData(form);
                data.set('currency_from', dropdunmng.getValue('currency1'));
                data.set('currency_to', dropdunmng.getValue('currency2'));

                let url = baseUrl + "transaction";
                fetch(url, {
                    body: data,
                    method: "POST",
                    headers: {
                        "Authorization": auth.getToken()
                    }
                }).then(res => res.json()).then(json => {
                    document.querySelector('#btnSign').disabled = true;
                    document.querySelector('#completed p').innerText = "¡Buenísimo! Tu solicitud de intercambio ha sido creada con exito, en unos minutos sera completada por uno de nuestros cajeros";
                    document.querySelector('#completed').style.display = 'block';
                });

            });

            convertmng.init("#amount", "#currency1", "#currency2", converted => {
                document.querySelector("#net").innerText = converted.net + " " + dropdunmng.getValue("currency2");
                document.querySelector("#comission").innerText = converted.comission + " " + dropdunmng.getValue("currency2");
                document.querySelector("#total").innerText = converted.total + " " + dropdunmng.getValue("currency2");
            });

            document.querySelector("#currency1").addEventListener('click', (ev) => {
                
                if(dropdunmng.getValue('currency1') == 'Paypal')
                    document.querySelector("#paypal_text").style.display = 'block';
                else
                    document.querySelector("#paypal_text").style.display = 'none';

            });

        }
    };

    let prestacash = {
        init() {
            auth.verify(["CLIENTE", "CAJERO"]);
            document.querySelector("#prestacash_max").innerText = auth.user().prestacash;
            
            let form = document.querySelector("#presta-form");
            form.addEventListener('submit', (ev) => {
                ev.preventDefault();

                if(!form.elements.terms.checked) return alert("Debe aceptar los términos y condiciones");

                let data = new FormData(form);
                let url = baseUrl + "application";
                fetch(url, {
                    body: data,
                    method: "POST",
                    headers: {
                        "Authorization": auth.getToken(),
                        "Accept": "application/json"
                    }
                }).then(res => res.json()).then(json => {
                    alert("Solicitud hecha correctamente, espere el resultado");
                    window.location.reload();
                });
            });

            let add_button = document.querySelector("#add-button");
            add_button.addEventListener('click', (ev) => {
                let val = document.querySelector("#add-value").value;
                let url = baseUrl + "user/prestacash/add/" + val;
                fetch(url, {
                    method: "PUT",
                    headers: {
                        "Authorization": auth.getToken(),
                        "Accept": "application/json"
                    }
                }).then(res => res.json()).then(json => {
                    
                    if(json.data == "OK") {
                        alert("Solicitud hecha correctamente");
                        auth.user().balance += Number(val);
                        auth.user().debt += Number(val);
                        document.querySelector("#close_session").click();
                    } else {
                        alert("Monto no válido");
                    }
                    
                });
            });

        }
    };

    let wallet_tasas = {
        init() {
            auth.verify(["CLIENTE", "CAJERO"]);
        }
    };

    let dashboard = {
        init() {
            auth.verify(['ADMIN', 'CAJERO']);
        }
    }

    /*
    EXEC FUNCTION
    */
    let element_user_img = document.querySelector('.wrapper .main .navbar img[alt="Charles Hall"]');

    //IF IS ON ADMIN PANEL
    if(element_user_img) {
        let element_user_text = element_user_img.nextElementSibling;
        element_user_text.innerText = auth.user().name;
        element_user_img.remove();

        let close_session = document.querySelector('#close_session');
        if(close_session) {
            close_session.addEventListener('click', (ev) => {
                ev.preventDefault();
                window.localStorage.clear();
                window.location.replace("pages-sign-in.html");
            });
        }

        if(auth.role() != "ADMIN") {
            document.querySelector('.sidebar-item a[href="pages-sign-up.html"]').style.display = 'none';
            document.querySelector('.sidebar-item a[href="ui-buttons.html"]').style.display = 'none';
            document.querySelector('.sidebar-item a[href="icons-feather.html"]').style.display = 'none';
        }
        
    }

    //IF IS ON WALLET
    let element_user_name = document.querySelector("#user_name1");
    if(element_user_name) {
        document.querySelector("#user_name1").innerText = auth.user().name;
        document.querySelector("#user_name2").innerText = auth.user().name;

        let close_session = document.querySelector('#close_session');
        if(close_session) {
            close_session.addEventListener('click', (ev) => {
                ev.preventDefault();
                window.localStorage.clear();
                window.location.replace(baseRedirect + "tecambiocash%20sistema%20de%20info/static/pages-sign-in.html");
            });
        }

    }

    //GENERAL
    if (location.protocol !== 'https:' || location.origin.includes("www")) {
        location.replace(baseRedirect + location.pathname.substring(1));
    }

    /*
    RETURN
    */
	return { index, login, register, divisas, addCashier, cajeros, services, agregar, retirar, enviar, cambiar, banks, wallet, prestacash, wallet_tasas, dashboard };
})();
