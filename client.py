# -*- coding: utf-8 -*-
import requests
import time
import json
import os


"""
Клиент для работы с Ammo crm
"""


class Ammo:

    supdomen = 'subdomen'
    headers = {'user-agent': 'amoCRM-API-client/1.0', 'content-type': 'application/json'}
    upd_errors = 'update_errors.txt'
    auth_data = {}
    post_param = {}
    _errors = {
        'not200': 'Не верный запрос',
        'write_f': 'Не удалось записать файл'
    }


    def __init__(self, login, key):
        self.auth_data = {'USER_LOGIN': login, 'USER_HASH': key}
        self.post_param = {'api_key': key, 'login': login}


    """
    Аутентификация
    """
    def _auth(self):
        url = 'https://' + self.supdomen + '.amocrm.ru/private/api/auth.php'
        self.session = requests.Session()
        self.session.get(url=url)
        authorisation = self.session.post(url=url, params=self.auth_data)
        if authorisation.status_code == 200:
            return True
        else:
            return False


    """
    Запись в JSON
    @name_file имя файла
    @arr массив
    """
    def _writeDump(self, name_file, arr):
        # Проверка на существование файла и удаление
        if os.path.isfile(name_file):
            os.remove(name_file)
        # Запись в файл
        with open(name_file, "w", encoding='utf8') as write_file:
            json.dump(arr, write_file, indent=4, ensure_ascii=False)

        if os.path.isfile(name_file):
            return True
        else:
            print(self._errors['write_f'])
            return False


    """
    Обработчик
    """
    def _collectAll(self, s=[]):
        arr_el = []
        for elem in s['_embedded']['items']:
            arr_el.append(elem)
        return arr_el


    """
    Получение всех элементов по обработчику
    """
    def _accumulate(self, api, func):
        name_file = 'data_' + api + '.json'
        new_arr = []
        offset = 0

        while True:
            url = 'https://' + self.supdomen + '.amocrm.ru/api/v2/' + api + '?limit_rows=500&limit_offset=' + str(offset)
            req = self.session.get(url=url, params=self.post_param, headers=self.headers)
            s = req.json()
            if 'response' in s:
                print(self._errors['auth'])
                return False

            if len(s['_embedded']['items']) < 500:
                new_arr += func(s)
                offset = 0
                break
            else:
                new_arr += func(s)
                offset += 500

        if self._writeDump(name_file, new_arr):
            return True
        else:
            return False


    """
    Получение всех компаний
    """
    def getCompanies(self):
        if not self._auth():
            return self._errors['auth']
        if self._accumulate('companies', self._collectAll):
            return True
        else:
            return False


    """
    Получение всех контактов
    """
    def getContacts(self):
        if not self._auth():
            return self._errors['auth']
        if self._accumulate('contacts', self._collectAll):
            return True
        else:
            return False


    """
    Получение всех лидов
    """
    def getLeads(self):
        if not self._auth():
            return self._errors['auth']
        if self._accumulate('leads', self._collectAll):
            return True
        else:
            return False


    """
    Получение всех задач
    """
    def getTasks(self):
        if not self._auth():
            return self._errors['auth']
        if self._accumulate('tasks', self._collectAll):
            return True
        else:
            return False


    """
    Получение всех событий
    """
    def getNotes(self):
        if not self._auth():
            return self._errors['auth']
        if self._accumulate('notes?type=task', self._collectAll):
            return True
        else:
            return False


    """
    Получение всех пользователей
    """
    def getUsers(self):
        if not self._auth():
            print(self._errors['not200'])
            return False

        name_file = 'data_users.json'
        url = 'https://' + self.supdomen + '.amocrm.ru/api/v2/account?with=users'
        req = self.session.get(url=url, params=self.post_param, headers=self.headers)
        uj = req.json()
        if 'response' in uj:
            print(uj['response']['error'])
            return False
        if self._writeDump(name_file, uj['_embedded']['users']):
            return True
        else:
            return False


    """
    Получение всех воронок
    """
    def getPipelines(self):
        if not self._auth():
            print(self._errors['not200'])
            return False

        name_file = 'data_pipelines.json'
        url = 'https://' + self.supdomen + '.amocrm.ru/api/v2/pipelines'
        req = self.session.get(url=url, params=self.post_param, headers=self.headers)
        uj = req.json()
        if 'response' in uj:
            print(uj['response']['error'])
            return False
        if self._writeDump(name_file, uj['_embedded']['items']):
            return True
        else:
            return False


    """
    Получение элемента по id
    """
    def getId(self, api, id, name_file='response.json'):
        if not self._auth():
            print(self._errors['not200'])
            return False
        url = 'https://' + self.supdomen + '.amocrm.ru/api/v2/' + api + '?id=' + str(id)
        send = self.session.get(url=url, params=self.post_param, headers=self.headers)
        resp = send.json()
        if 'response' in resp:
            print(resp['response']['error'])
            return False
        if self._writeDump(name_file, resp):
            print('Данные сохранены в ' + name_file)
            return True
        else:
            return False


    """
    Создание, обновление сущностей
    @arr массив элементов
    """
    def job(self, api, method, arr):
        first_resp = None
        if method == 'update' or method == 'add':
            if not self._auth():
                print(self._errors['not200'])
                return False
            url = 'https://' + self.supdomen + '.amocrm.ru/api/v2/' + str(api)
            str_err = '' # Строка ошибок
            pack_arr = []# Пакеты запросов
            j = 1
            i = 0
            k = 100
            while True:
                e = arr[i:k]
                if len(e) < 100:
                    if e:
                        pack_arr.append(arr[i:k])
                    break
                else:
                    pack_arr.append(arr[i:k])
                    i = k
                    k += 100
            del(arr)
            print('Подождите... Необходимо ', len(pack_arr), ' запросов.')
            for el in pack_arr:
                json_data = {method: el}
                send = self.session.post(url=url, params=self.post_param, json=json_data, headers=self.headers)
                print(j, ' - статус ответа ', send.status_code)
                j += 1
                if send.status_code != 200:
                    print(j, ' - статус ответа ', send.status_code, ' - обработан! Запрос - ', json_data,)
                    str_err += 'Статус ' + str(send.status_code) + ' запрос - ' + json.dumps(el, ensure_ascii=False) + '\n'
                    j += 1
                    continue
                resp = send.json()
                first_resp = resp
                if 'response' in resp:
                    print(resp['response']['error'])
                    return False
                if 'errors' in resp['_embedded']:
                    re = resp['_embedded']['errors'][method]
                    if type(re) == list:
                        for el in re:
                            str_err += json.dumps(el, ensure_ascii=False) + '\n'
                    else:
                        for el in re:
                            str_err += str(el) + ' - ' + re[el] + '\n'
            else:
                if str_err:
                    with open(self.upd_errors, "w", encoding='utf8') as write_file:
                        write_file.write(str_err)
                        print('Не все элементы обновились, подробнее - ' + self.upd_errors)
                return first_resp
        else:
            print('Нет обработки для метода ', method)
            return False



    """
    Получение всех событий по смене статуса сделки для всех сделок
    @gl_arr словарь запросов
    """
    def lastStatusLeads(self, gl_arr):
        if not self._auth():
            print(self._errors['not200'])
            return False
        all_st_j = []
        ll = 0
        for str_req in gl_arr:
            time.sleep(0.2)
            url = 'https://vfnd.amocrm.ru/api/v2/notes?note_type=3&type=lead' + str_req
            resp = self.session.get(url=url, params=self.post_param, headers=self.headers)
            ll += 1
            if resp.status_code == 200:
                er_ar = resp.json()
                all_st_j.extend(er_ar['_embedded']['items'])
        else:
            if self._writeDump('last_status.json', all_st_j):
                return True
            else:
                return False


