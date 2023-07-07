#!/usr/bin/env python
from scipy import optimize
import numpy as np
from numpy import genfromtxt
import sys, getopt
import mysql.connector
import json

dataPotencia = []
precioTerminoPotencia = []
xant = []
xcontr = []
xopt = []

def read_data(conexion, dates):
	global dataPotencia, precioTerminoPotencia, xant, xcontr, xopt
	status = {}
	status["error"] = True
	status["msg_error"] = "Unexpected ending process"
	try:
		cnx = mysql.connector.connect(host=conexion['servername'], port=conexion['port'], user=conexion['username'], database=conexion['database'], password=conexion['password'])
	except mysql.connector.Error as err:
		status["error"] = True
		status["msg_error"] = err.msg
		return status
	else:
		strSQL = ("SELECT MONTH(date) AS month, YEAR(date) AS year, 4*`EAct imp(kWh)` AS potencia, RIGHT(Periodo,1) as periodo, DATEDIFF(date, '1970-01-01') AS days_unix "
				  "FROM `ZPI_Contador_Festivos_Periodos`"
				  "WHERE date >= %s AND date <= %s")
		cursor = cnx.cursor()
		try:
			cursor.execute(strSQL, (dates['begin_date'], dates['end_date']))
		except mysql.connector.Error as err:
			status["error"] = True
			status["msg_error"] = err.msg
			cnx.close()
			return status
		else:
			for row in cursor:
				auxRow = [row[0], row[1], float(row[2]), int(row[3]), int(row[4])]
				dataPotencia.append(auxRow)

		precioTerminoPotencia = np.zeros(6)

		strSQL = "SELECT RIGHT(Periodo,1) as periodo, Precio as precio FROM ZPI_Precio_Potencia_Contratada ORDER BY Periodo"
		try:
			cursor.execute(strSQL)
		except mysql.connector.Error as err:
			status["error"] = True
			status["msg_error"] = err.msg
			cnx.close()
			return status
		else:
			for row in cursor:
				idx = int(row[0])
				precioTerminoPotencia[idx - 1] = float(row[1])

		

		xant = np.zeros(6)
		xcontr = np.zeros(6)

		strSQL = "SELECT RIGHT(Periodo,1) as periodo, Potencia_contratada FROM Potencia_Contratada ORDER BY Periodo"
		try:
			cursor.execute(strSQL)
		except mysql.connector.Error as err:
			status["error"] = True
			status["msg_error"] = err.msg
			cnx.close()
			return status
		else:
			for row in cursor:
				idx = int(row[0])
				xcontr[idx - 1] = float(row[1])
				xant[idx - 1] = float(row[1])
		
		xopt = np.zeros(6)

		strSQL = "SELECT RIGHT(Periodo,1) as periodo, Potencia_contratada FROM Potencia_Contratada_Optima ORDER BY Periodo"
		try:
			cursor.execute(strSQL)
		except mysql.connector.Error as err:
			status["error"] = True
			status["msg_error"] = err.msg
			cnx.close()
			return status
		else:
			for row in cursor:
				idx = int(row[0])
				xopt[idx - 1] = float(row[1])

		#strSQL = "SELECT * FROM `Coste_Exceso_Potencia_Optima` WHERE `date`>=%s AND `date`<=%s"

		#try:
		#	cursor.execute(strSQL, (dates['begin_date'], dates['end_date']))
		#except mysql.connector.Error as err:
		#	status["error"] = True
		#	status["msg_error"] = err.msg
		#	cnx.close()
		#	return status
		#else:
		#	for row in cursor:
		#		print row[0], ", ", row[1], ", ", row[2], ", ", row[3], ", ", row[4], ", ", row[5], ", " , row[6], "<br/>"
		#print "--------------------------------------------------------"

		cnx.close()
		status["error"] = False
		status["msg_error"] = ""
		return status
	return status

def funct_consumo(x0):
	global precioTerminoPotencia, dataPotencia, xcontr
	x0a = np.array(x0)
	ki = np.array([1.0, 0.5, 0.37, 0.37, 0.37, 0.17])
	mesesDisponibles = 12
	diasPotencia = 365
	dataAnios = np.zeros((6, 1200))
	
	minDay = 1e152
	maxDay = -1e152

	for i in range(0,len(dataPotencia)):
		if dataPotencia[i][4] > maxDay:
			maxDay = dataPotencia[i][4]
		if dataPotencia[i][4] < minDay:
			minDay = dataPotencia[i][4]
	dataDays = np.zeros(maxDay - minDay + 1)
	
	for i in range(0,len(dataPotencia)):
		idxDay = dataPotencia[i][4] - minDay
		dataDays[idxDay] = 1
	daysMeasured = sum(dataDays)
	
	for i in range(0,len(dataPotencia)):
		if(dataPotencia[i][2] > x0[int(dataPotencia[i][3]) - 1]):
			idxMonth = (dataPotencia[i][1] - 1970)*12 + dataPotencia[i][0] - 1
			dataAnios[dataPotencia[i][3] - 1][idxMonth] += (dataPotencia[i][2] - x0[dataPotencia[i][3] - 1])**2
			#print (dataPotencia[i][2] - x0[dataPotencia[i][3] - 1])**2
			#print " "
			#print dataPotencia[i], "P", dataPotencia[i][3]
			#print "<br>"
	#np.set_printoptions(threshold='nan')
	#print np.transpose(dataAnios)	
	#dataAnios **= 0.5
	dataAnios **= 0.5
	aei = np.sum(dataAnios,1)
	fpe = 1.4064* aei * ki
	#print x0
	#print fpe
	#sys.exit(1)
	fp = fpe + daysMeasured * mesesDisponibles*(x0a*precioTerminoPotencia) / diasPotencia
	fx = sum(fp)	
	return fx

def func_cons_deriv(x0):
	eps = 1e-10
	dfx = np.zeros(len(x0))
	for i in range(0,len(x0)):
		xaux1 = np.copy(x0)
		xaux1[i] += eps
		xaux2 = np.copy(x0)
		xaux2[i] -= eps
		faux1 = funct_consumo(xaux1)
		faux2 = funct_consumo(xaux2)
		dfx[i] = (faux1 - faux2) / (2*eps)
	return dfx

def optimize_cons(conexion, x0):
	global xcontr
	status = {}
	status["error"] = True
	status["msg_error"] = ""

	cons = ({'type': 'ineq',
			'fun' : lambda x: np.array([-x[0] + x[1]]),
			'jac' : lambda x: np.array([-1.0, 1.0, 0.0, 0.0, 0.0, 0.0])},
		{'type': 'ineq',
			'fun' : lambda x: np.array([-x[1] + x[2]]),
			'jac' : lambda x: np.array([0.0, -1.0, 1.0, 0.0, 0.0, 0.0])},
		{'type': 'ineq',
			'fun' : lambda x: np.array([-x[2] + x[3]]),
			'jac' : lambda x: np.array([0.0, 0.0, -1.0, 1.0, 0.0, 0.0])},
		{'type': 'ineq',
			'fun' : lambda x: np.array([-x[3] + x[4]]),
			'jac' : lambda x: np.array([0.0, 0.0, 0.0, -1.0, 1.0, 0.0])},
		{'type': 'ineq',
			'fun' : lambda x: np.array([-x[4] + x[5]]),
			'jac' : lambda x: np.array([0.0, 0.0, 0.0, 0.0, -1.0, 1.0])},
		{'type': 'ineq',
			'fun' : lambda x: np.array([x[5] - 450]),
			'jac' : lambda x: np.array([0.0, 0.0, 0.0, 0.0, 0.0, 1.0])},
		{'type': 'ineq',
			'fun' : lambda x: np.array([x[5] - xcontr[5]]),
			'jac' : lambda x: np.array([0.0, 0.0, 0.0, 0.0, 0.0, 1.0])})


	bnds = [[1.0, 10000000], [1.0, 10000000], [1.0, 10000000], [1.0, 10000000], [1.0, 10000000], [1.0, 10000000]]

	try:
		res = optimize.minimize(funct_consumo, x0, bounds=bnds, jac=func_cons_deriv, constraints=cons, method='SLSQP', options={'disp': False})
		#print res
		#sys.exit(1)
	except Exception as e:
		print e
		status["error"] = True
		status["msg_error"] = "Optimizer error"
		return status
	else:
		try:
			cnx = mysql.connector.connect(host=conexion['servername'], port=conexion['port'], user=conexion['username'], database=conexion['database'], password=conexion['password'])
		except mysql.connector.Error as err:
			status["error"] = True
			status["msg_error"] = err.msg
			return status
		else:
			cursor = cnx.cursor()
			for idx in range(0,6):
				res.x[idx] = int(round(res.x[idx], 0))
				strUpdate = "UPDATE Potencia_Contratada_Optima SET Potencia_contratada='%s' WHERE Periodo=%s"
				period = "P" + str(idx + 1)
				try:
					cursor.execute(strUpdate, (int(round(res.x[idx], 0)), period))
				except mysql.connector.Error as err:
					print cursor.statement
					status["error"] = True
					status["msg_error"] = err.msg
					cnx.close()
					return status
			status["error"] = False
			status["msg_error"] = ""
			status["x"] = res.x
			status["fx"] = funct_consumo(res.x)
			return status
	return status

def main(argv):
	global xant
	conexion = {}
	conexion['servername'] = ''
	conexion['username'] = ''
	conexion['password'] = ''
	conexion['database'] = ''
	conexion['port'] = '3306'

	dates = {}
	dates["begin_date"] = ''
	dates["end_date"] = ''
	
	try:
	  opts, args = getopt.getopt(argv,"hs:p:u:k:d:b:e:",["server=","port=","username=","password=","database=","begin_date=", "end_date="])
	except getopt.GetoptError:
	  print 'optimization.py -s <server_address> -p <port> -u <username> -k <password> -d <database> -b <begin_date> -e <end_date>'
	  sys.exit(2)
	for opt, arg in opts:
		if opt == '-h':
			print 'optimization.py -s <server> -p<port> -u <username> -k <password> -d <database> -b <begin_date> -e <end_date>'
		 	sys.exit()
	  	elif opt in ("-s", "--server"):
		 	conexion['servername'] = arg
		elif opt in ("-u", "--username"):
			conexion['username'] = arg
		elif opt in ("-k", "--password"):
			conexion['password'] = arg
		elif opt in ("-d", "--database"):
			conexion['database'] = arg
		elif opt in ("-p", "--port"):
			conexion['port'] = arg
		elif opt in ("-b", "--begin_date"):
			dates['begin_date'] = arg
		elif opt in ("-e", "--end_date"):
			dates['end_date'] = arg

	status = read_data(conexion, dates)
	if(status["error"]):
		print json.dumps(status)
		sys.exit(1)

	for i in range(0,5):
		xant[i] = 1.0

	status = optimize_cons(conexion, xopt)
	if(status["error"]):
		print json.dumps(status)
		sys.exit(1)
	status = {"error": False, "fx": status["fx"], "x":status["x"].tolist()}
	print json.dumps(status)

if __name__ == "__main__":
	main(sys.argv[1:])



