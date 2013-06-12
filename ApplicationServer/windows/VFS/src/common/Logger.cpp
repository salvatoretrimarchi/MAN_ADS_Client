// Copyright (C) 2012 
// Author Wei-Jen Chen 2012

#include "stdafx.h"
#include "Logger.h"

#include <windows.h>
#include <fstream>
#include <iostream>
#include <stdlib.h>
#include <stdio.h>
#include <ctime>
#include "WinBase.h"
#include <iostream>

Logger* Logger::m_sInstance = NULL;

Logger::Logger()
{
	// TODO user do not have the right to write here !!!
	m_szLogFile = L"";
	m_bIsLogging = false;
	this->logLevel = LOG_INFO;
	this->useStdOut = true;
}
Logger::~Logger()
{
}

Logger& Logger::getSingleton()
{
	if(m_sInstance == NULL) 
	{
		m_sInstance = new Logger();
	}
	return (*m_sInstance);
}

Logger* Logger::getSingletonPtr()
{
	if(m_sInstance == NULL) 
	{
		m_sInstance = new Logger();
	}
	return m_sInstance;
}


Logger::Level Logger::getFromString(std::wstring& level) {
	if( level.compare(L"DEBUG") == 0)
		return LOG_DEBUG;

	if( level.compare(L"INFO") == 0)
		return LOG_INFO;

	if( level.compare(L"WARN") == 0)
		return LOG_WARN;

	if( level.compare(L"ERROR") == 0)
		return LOG_ERROR;

	throw UException(L"Unsupported log level");
}


void Logger::getLevelString(std::wstring& level) {
	if (this->logLevel == LOG_DEBUG)
		level = L"DEBUG";

	if (this->logLevel == LOG_INFO)
		level = L"INFO";

	if (this->logLevel == LOG_WARN)
		level = L"WARN";

	if (this->logLevel == LOG_ERROR)
		level = L"ERROR";

	if (level.empty())
		level = L"UNKNOW";
}



void Logger::setLogFile(std::wstring szLogFile)
{
	m_szLogFile = szLogFile;
}


void Logger::setLevel(Level lvl) {
	this->logLevel = lvl;
}


void Logger::setStdoutput(bool value) {
	this->useStdOut = value;
}



void Logger::debug(const wchar_t* format,...)
{
	#define MAX_DBG_MSG_LEN (4096)
    wchar_t buf[MAX_DBG_MSG_LEN];
	wchar_t msg[MAX_DBG_MSG_LEN];
	
	wchar_t modname[200];
	GetModuleFileName(NULL, modname, sizeof(modname));
	
	va_list args;
    va_start(args, format);
	vswprintf_s(buf, format, args);
    va_end(args);

	msg[0] = '\0';
	lstrcat(msg, modname);
	lstrcat(msg, L": ");
	lstrcat(msg, buf);

	std::wstring m = msg;
	if (m.find(L"Dbgview") != std::string::npos)
		return;

    OutputDebugString(msg);
}

void Logger::log(Level lvl, wchar_t *fmt,...) {
	if (lvl < this->logLevel)
		return;

	m_bIsLogging = true;

	va_list args;

	wchar_t temp[5000];
	std::wofstream out(m_szLogFile.c_str());
	if (out.good())
		out.seekp(0, std::ios::end);

	time_t rawtime;
	struct tm timeinfo;
	time(&rawtime);
	if (localtime_s(&timeinfo, &rawtime)) {
		this->debug(L"Failed to get localtime: errno %i", errno);
		return;
	}
	wsprintf(temp, L"[%d/%02d/%02d %02d:%02d:%02d] ",
		timeinfo.tm_year + 1900,
		timeinfo.tm_mon + 1,
		timeinfo.tm_mday,
		timeinfo.tm_hour,
		timeinfo.tm_min,
		timeinfo.tm_sec);
	
	if (out.good())
		out<<temp;

	if (this->useStdOut)
		std::wcout<<temp;

	wchar_t modname[200];
	GetModuleFileName(NULL, modname, sizeof(modname));
	wsprintf(temp, L"%s : ", modname);
	
	if (out.good())
		out<<temp;

	if (this->useStdOut)
		std::wcout<<temp;

	va_start(args,fmt);
	vswprintf_s(temp, fmt, args);
	va_end(args);

	if (out.good())
		out<<temp;

	if (this->useStdOut)
		std::wcout<<temp;

	wsprintf(temp, L"\r\n");

	if (out.good()) {
		out<<temp;
		out.close();
	}
	
	if (this->useStdOut)
		std::wcout<<temp;

	m_bIsLogging = false;
}
